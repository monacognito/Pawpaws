create table purchases
(
    id         int not null primary key auto_increment,
    item_id    int not null,
    amount     int not null,
    total      int not null,
    created_at datetime default current_timestamp,

    foreign key (item_id) references items (id)
);
