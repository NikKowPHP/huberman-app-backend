-- Ensures new users in Supabase auth are copied to the public users table
create or replace function public.handle_new_user()
returns trigger
language plpgsql
security definer set search_path = public
as $$
begin
  insert into public."User" (id, email, name) -- Ensure table and column names match your Prisma schema exactly ("User")
  values (new.id, new.email, new.raw_user_meta_data->>'name');
  return new;
end;
$$;

-- drop trigger if exists on_auth_user_created on auth.users; -- uncomment to reset
create or replace trigger on_auth_user_created
  after insert on auth.users
  for each row execute procedure public.handle_new_user();