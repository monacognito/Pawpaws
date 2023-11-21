create table groomings (
  id int not null primary key auto_increment,
  member_id int not null,
  created_at datetime default current_timestamp,
  groom_date date not null,
  groom_time time not null,
  price int not null,
  is_paid boolean 

  foreign key (member_id) references members(id)
);

insert into groomings value (
  default,
  2,
  default, 
  "2024-10-10", 
  "23:23", 
  20000, 
  false 
);
