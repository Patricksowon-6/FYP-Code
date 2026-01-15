drop table if exists insta_users;

create table insta_users 
(
	username varchar(30) primary key,
	first_name char(20), 
	last_name char(20), 
	signup_date date
);

insert into insta_users values ('lucky_cat', 'andrea', 'costa', '2021-09-27');
insert into insta_users values ('unlucky_dog', 'sebastian', 'clover', '2023-02-03');
insert into insta_users values ('brave_fish', 'matt', 'ball', '2000-12-17');
insert into insta_users values ('inspirational_lion', 'dan', 'simb', '1967-11-03');

select * from insta_users;