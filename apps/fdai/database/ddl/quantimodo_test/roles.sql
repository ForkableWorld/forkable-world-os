create table quantimodo_test.roles
(
    id          int unsigned auto_increment
        primary key,
    name        varchar(255)  not null,
    slug        varchar(255)  not null,
    description varchar(255)  null,
    level       int default 1 not null,
    created_at  timestamp     null,
    updated_at  timestamp     null,
    deleted_at  timestamp     null,
    constraint roles_slug_unique
        unique (slug)
)
    collate = utf8mb3_unicode_ci;

