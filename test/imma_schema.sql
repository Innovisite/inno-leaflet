CREATE TABLE imma (
siren text primary key,
denomination text,
longitude integer,
latitude integer
);

create table clusters (
id integer primary key,
longitude integer,
latitude integer,
minZoom integer,
maxZoom integer,
size integer
);
