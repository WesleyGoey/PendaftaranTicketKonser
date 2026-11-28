-- Jalankan seluruh isi file ini di Supabase: SQL Editor > New query > Run.
--
-- PERHATIAN: file ini MENGHAPUS lalu membuat ulang tabel public.concerts dan
-- public.ticket_purchases (semua isinya hilang). Tabel lain, misalnya students,
-- dan akun di menu Authentication TIDAK disentuh.

drop function if exists public.book_tickets(bigint, integer, text, text);
drop table if exists public.ticket_purchases;
drop table if exists public.concerts;

create table public.concerts (
    id                bigint generated always as identity primary key,
    title             text not null,
    artist            text not null,
    concert_date      timestamp not null,
    venue             text not null,
    total_tickets     integer not null check (total_tickets >= 0),
    available_tickets integer not null check (available_tickets >= 0),
    price             numeric(12, 2) not null,
    description       text
);

create table public.ticket_purchases (
    id                bigint generated always as identity primary key,
    confirmation_code text not null unique,
    user_id           uuid not null references auth.users (id) on delete cascade,
    concert_id        bigint not null references public.concerts (id) on delete cascade,
    phone             text not null,
    quantity          integer not null check (quantity > 0),
    seat_type         text not null check (seat_type in ('regular', 'premium', 'vip', 'diamond')),
    total_price       numeric(12, 2) not null,
    status            text not null default 'pending',
    created_at        timestamptz not null default now()
);

-- Row Level Security: publishable key saja tidak cukup untuk membaca data pembeli.
alter table public.concerts enable row level security;
alter table public.ticket_purchases enable row level security;

create policy concerts_public_read on public.concerts
    for select to anon, authenticated using (true);

create policy purchases_read_own on public.ticket_purchases
    for select to authenticated using ((select auth.uid()) = user_id);

revoke all on public.concerts, public.ticket_purchases from anon, authenticated;
grant select on public.concerts to anon, authenticated;
grant select on public.ticket_purchases to authenticated;

-- Satu-satunya cara membuat pesanan. Stok dikunci supaya tidak terjual berlebih,
-- dan harga ditentukan di sini (harus sama dengan config/tickets.php).
create function public.book_tickets(
    p_concert_id bigint,
    p_quantity integer,
    p_seat_type text,
    p_phone text
) returns jsonb
language plpgsql
security definer
set search_path = ''
as $$
declare
    v_user uuid := auth.uid();
    v_price numeric;
    v_available integer;
    v_purchase public.ticket_purchases;
begin
    if v_user is null then
        raise exception 'not_authenticated';
    end if;

    v_price := case p_seat_type
        when 'regular' then 1000000
        when 'premium' then 2000000
        when 'vip' then 3000000
        when 'diamond' then 4000000
    end;

    if v_price is null or p_quantity is null or p_quantity < 1
        or p_phone is null or length(trim(p_phone)) = 0 or length(p_phone) > 30 then
        raise exception 'invalid_booking';
    end if;

    select available_tickets into v_available
    from public.concerts where id = p_concert_id for update;

    if v_available is null then
        raise exception 'concert_not_found';
    end if;

    if v_available < p_quantity then
        raise exception 'not_enough_tickets';
    end if;

    update public.concerts
    set available_tickets = available_tickets - p_quantity
    where id = p_concert_id;

    insert into public.ticket_purchases
        (confirmation_code, user_id, concert_id, phone, quantity, seat_type, total_price)
    values
        (upper(substr(replace(gen_random_uuid()::text, '-', ''), 1, 8)),
         v_user, p_concert_id, p_phone, p_quantity, p_seat_type, v_price * p_quantity)
    returning * into v_purchase;

    return to_jsonb(v_purchase);
end;
$$;

revoke execute on function public.book_tickets(bigint, integer, text, text) from public, anon;
grant execute on function public.book_tickets(bigint, integer, text, text) to authenticated;

-- Realtime untuk My Tickets: setiap perubahan pesanan "menyentuh" konsernya, sehingga
-- channel concerts mengirim sinyal. Data pembeli sendiri tidak pernah disiarkan.
create or replace function public.touch_concert_after_purchase_change()
returns trigger
language plpgsql
security definer
set search_path = ''
as $$
begin
    update public.concerts
    set available_tickets = available_tickets
    where id in (
        case when tg_op <> 'DELETE' then new.concert_id end,
        case when tg_op <> 'INSERT' then old.concert_id end
    );
    return null;
end;
$$;

revoke execute on function public.touch_concert_after_purchase_change() from public, anon, authenticated;

drop trigger if exists ticket_purchases_realtime on public.ticket_purchases;
create trigger ticket_purchases_realtime
    after insert or update or delete on public.ticket_purchases
    for each row execute function public.touch_concert_after_purchase_change();

-- Aktifkan Realtime hanya untuk concerts.
alter publication supabase_realtime add table public.concerts;

insert into public.concerts
    (title, artist, concert_date, venue, total_tickets, available_tickets, price, description)
values
    ('Bruno Mars Live in Surabaya', 'Bruno Mars', '2027-02-09 18:00:00',
     'Universitas Ciputra Surabaya, Indonesia', 100, 100, 1500000,
     'A spectacular night of Bruno Mars hits, soulful vocals, and unforgettable live performances.');
