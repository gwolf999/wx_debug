drop table if exists media;

create table media(
	id int not null auto_increment primary key,
	filename char(50) not null default '',
	rtype char(10) not null default '',
	media_id char(200) not null default '', 
	created_at int not null default 0
);