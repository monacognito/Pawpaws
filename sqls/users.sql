create table users (
  id int not null primary key auto_increment,
  username varchar(50) not null unique,
  password varchar(255) not null,
  created_at datetime default current_timestamp   
);

insert into users value (
  1,
  "admin",
  "admin",
  default
);