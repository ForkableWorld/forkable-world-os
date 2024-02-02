create table quantimodo_test.cache
(
    `key`      varchar(255) not null,
    value      mediumtext   not null,
    expiration int          not null,
    constraint cache_key_unique
        unique (`key`)
)
    collate = utf8mb4_unicode_ci;

