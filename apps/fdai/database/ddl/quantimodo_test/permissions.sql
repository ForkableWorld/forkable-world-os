create table quantimodo_test.permissions
(
    id          int unsigned auto_increment
        primary key,
    name        varchar(255) not null,
    slug        varchar(255) not null,
    description varchar(255) null,
    model       varchar(255) null,
    created_at  timestamp    null,
    updated_at  timestamp    null,
    deleted_at  timestamp    null,
    constraint permissions_slug_unique
        unique (slug)
)
    collate = utf8mb3_unicode_ci;

