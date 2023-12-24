-- Items

insert into items
values (default, "Maine Coon Purr 1kg", "Makanan kucing untuk Maine Coon", "75000", 30),
       (default, "Dogge Steer Chews 2 pack", "Treat untuk doggy ", "27000", 15),
       (default, "Whoops no Flee Shampoo", "Shampoo anti kutu 250ml", "48500", 40),
       (default, "Catnip UwU", "Catnip untuk menenangkan kucing", "17500", 9),
       (default, "Adored Meow Vitamin 200ml", "Vitamin untuk kucing", "80000", 10),
       (default, "Porcupine squicky toy", "Mainan berbunyi saat dipencet", "15000", 27),
       (default, "Cat Dog Brush", "Sisir untuk doggy dan kucing", "24000", 15),
       (default, "Bone Chew Toy", "Mainan bentuk tulang untuk doggy", "13500", 33),
       (default, "Meow meow Bed", "Kasur untuk kucing", "100000", 22),
       (default, "Big Dogge Pillow", "Bantal besar untuk tidur doggy", "100000", 30),
       (default, "Mouse on Rod", "Makanan kucing dengan model pancingan", "25000", 17),
       (default, "Doggo Toothbrush", "Sikat gigi untuk doggy", "12500", 20);


-- Members

insert into members
values (default, "Cheems", "Shiba Inu", 'f', "084450923910", "Bruh St. 333", "2018-12-10 14:33:33", default),
       (default, "Mew", "British Short Hair", 'f', "083214653610", "Neverland Eve 21C", "2020-04-02 10:33:33", default),
       (default, "Koma", "Ragdoll", 'f', "081114561622", "Tiktik 123", "2021-02-01 08:33:33", default),
       (default, "Jeruk", "Persian", 'm', "088086651213", "Hachi 1Z", "2022-04-02 13:02:59", default),
       (default, "Ben", "American Shorthair", 'm', "0813082653211", "NY Street 12W", "2023-01-09 12:02:39", default),
       (default, "Fanni", "Sphynx", 'm', "0831080651235", "ROG 1513C", "2023-02-17 05:02:52", default),
       (default, "Manis", "Sugar Glider", 'f', "081187351903", "Ryme Town 2VE", "2018-01-07 07:11:40", default),
       (default, "Zela", "Green sea turtle", 'm', "08125457218", "Hachi 1Z", "2023-04-02 13:02:59", default),
       (default, "Pela", "Softshell turtle", 'f', "08535157209", "Aquala MZ2", "2023-01-01 23:02:59", default),
       (default, "Si Boy", "Golden retriever", 'm', "08518557912", "Hutan Mangrove A2", "2023-01-10 23:50:59", default);

-- Groomings

insert into groomings
values (default, 2, default, curdate(), "15:23", 25000, false),
       (default, 5, default, curdate(), "13:23", 22000, true),
       (default, 3, default, curdate(), "11:23", 50000, false),
       (default, 1, default, curdate(), "10:23", 50000, true),
       (default, 1, default, "2023-10-10", "10:23", 20000, true),
       (default, 4, default, "2023-09-10", "15:23", 20000, true),
       (default, 5, default, "2023-11-10", "10:00", 40000, false),
       (default, 7, default, "2023-11-10", "14:00", 37000, false),
       (default, 9, default, "2023-05-11", "10:20", 67000, true);

