create table quantimodo_test.notifications
(
    id              char(36)        not null
        primary key,
    type            varchar(255)    not null,
    notifiable_type varchar(255)    not null,
    notifiable_id   bigint unsigned not null,
    data            text            not null,
    read_at         timestamp       null,
    created_at      timestamp       null,
    updated_at      timestamp       null,
    deleted_at      timestamp       null
)
    collate = utf8mb3_unicode_ci;

create index notifications_notifiable_type_notifiable_id_index
    on quantimodo_test.notifications (notifiable_type, notifiable_id);

