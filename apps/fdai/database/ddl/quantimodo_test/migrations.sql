create table quantimodo_test.migrations
(
    migration  varchar(255)                        not null,
    batch      int                                 not null,
    updated_at timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    created_at timestamp default CURRENT_TIMESTAMP not null,
    deleted_at timestamp                           null
)
    charset = utf8mb3;

