create table contacts
(
    id      int auto_increment comment 'A rekord azonosítója, technikai mező',
    name    varchar(255) not null comment 'A kontakt neve',
    phone   varchar(255) not null comment 'A kontakt telefonszáma',
    address varchar(255) not null comment 'A kontakt címe',
    constraint contacts_pk
        primary key (id)
)
    comment 'Kontaktok táblája';
