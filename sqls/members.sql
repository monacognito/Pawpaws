create table members (
  id int not null primary key auto_increment,
  name varchar(50) not null,
  type varchar(50) not null,
  gender enum('m', 'f') not null,
  owner_mobile varchar(14) not null,
  address varchar(50),
  created_at datetime default current_timestamp,   
  expired_at datetime default (created_at + interval 6 month)
);