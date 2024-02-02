create table quantimodo_test.health_checks
(
    id             int unsigned auto_increment
        primary key,
    resource_name  varchar(255)                        not null,
    resource_slug  varchar(255)                        not null,
    target_name    varchar(255)                        not null,
    target_slug    varchar(255)                        not null,
    target_display varchar(255)                        not null,
    healthy        tinyint(1)                          not null,
    error_message  text                                null,
    runtime        double(8, 2)                        not null,
    value          varchar(255)                        null,
    value_human    varchar(255)                        null,
    created_at     timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    charset = utf8mb3;

create index health_checks_created_at_index
    on quantimodo_test.health_checks (created_at);

create index health_checks_resource_slug_index
    on quantimodo_test.health_checks (resource_slug);

create index health_checks_target_slug_index
    on quantimodo_test.health_checks (target_slug);

