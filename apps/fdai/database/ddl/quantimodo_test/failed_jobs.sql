create table quantimodo_test.failed_jobs
(
    id         bigint unsigned auto_increment
        primary key,
    connection text                                not null,
    queue      text                                not null,
    payload    longtext                            not null,
    exception  longtext                            not null,
    failed_at  timestamp default CURRENT_TIMESTAMP not null,
    uuid       varchar(255)                        not null,
    constraint failed_jobs_uuid_unique
        unique (uuid)
)
    charset = utf8mb3;

