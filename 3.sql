create table emails
(
    id         int auto_increment comment 'A rekord azonosítója, technikai mező',
    email      varchar(255) not null comment 'Az e-mail cím',
    contact_id int          not null comment 'Az e-mail címhez tartozó kontakt azonosítója, a contact táblában',
    constraint emails_pk
        primary key (id),
    constraint emails_uk
        unique (email),
    constraint emails_contacts_id_fk
        foreign key (contact_id) references contacts (id)
)
    comment 'E-mail címek táblája';
