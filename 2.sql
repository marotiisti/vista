create table companies
(
    id           int auto_increment comment 'A rekord azonosítója, technikai mező',
    name         varchar(255)     not null comment 'A cég neve',
    address      varchar(255)     not null comment 'A cég címe',
    contact_id   int default NULL null comment 'A céghez tartozó kontakt azonosító, a contact táblában',
    constraint companies_pk
        primary key (id),
    constraint companies_contacts_id_fk
        foreign key (contact_id) references contacts (id)
)
    comment 'Cégek táblája';
