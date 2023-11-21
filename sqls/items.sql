create table items (
  id int not null primary key auto_increment,
  name varchar(50) not null,
  description varchar(100),
  price int not null,
  stock int not null default 0
);

insert into items value (
  default,
  "Dog Food Canine 500g",
  "Dog Food kering untuk anjing ukuram 5-10kg",
  "40000",
  50
);
insert into items value (
  default,
  "Cat Food Whiskiz 480g",
  "Makanan kucing sedang",
  "35000",
  22
);