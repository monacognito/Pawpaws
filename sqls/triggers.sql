delimiter //
create trigger before_delete_member
  before delete
  on members for each row
begin
  delete from groomings where member_id = old.id;
end
//

delimiter ;


delimiter //
create trigger before_update_item
  before update
  on items for each row
begin
  if new.stock < old.stock then
    insert into purchases value (
      default,
      new.id,
      old.stock - new.stock,
      new.price * (old.stock - new.stock),
      default
    );
  end if;
end
//

delimiter ;