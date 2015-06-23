drop table if exists wuser;

create table wuser(
	openid char(100) not null primary key,
	groupid int not null default 0,
	subscribe int not null default 0,
	nickname CHAR(50) not null  DEFAULT '',
	sex int not null  default 0,
	city char(50) not null default '',
	country char(50) NOT NULL DEFAULT '',
	province char(50) NOT NULL DEFAULT '',
	headimgurl char(255) not null default '',
	subscribe_time int not null default 0
);