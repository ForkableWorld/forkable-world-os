create table quantimodo_test.oauth_auth_codes
(
    id         varchar(100) not null
        primary key,
    user_id    bigint       not null,
    client_id  int unsigned not null,
    scopes     text         null,
    revoked    tinyint(1)   not null,
    expires_at datetime     null
)
    collate = utf8mb3_unicode_ci;

