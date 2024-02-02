create table quantimodo_test.tags
(
    id           int unsigned auto_increment
        primary key,
    name         json         not null,
    slug         json         not null,
    type         varchar(255) null,
    order_column int          null,
    created_at   timestamp    null,
    updated_at   timestamp    null
)
    collate = utf8mb3_unicode_ci;

