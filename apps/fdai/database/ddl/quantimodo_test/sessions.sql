create table quantimodo_test.sessions
(
    id            varchar(255)    not null,
    user_id       bigint unsigned null,
    ip_address    varchar(45)     null,
    user_agent    text            null,
    payload       text            not null,
    last_activity int             not null,
    constraint sessions_id_unique
        unique (id)
)
    collate = utf8mb4_unicode_ci;

