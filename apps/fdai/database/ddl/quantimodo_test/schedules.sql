create table quantimodo_test.schedules
(
    id                       int unsigned auto_increment
        primary key,
    command                  varchar(255)         not null,
    command_custom           varchar(255)         null,
    params                   text                 null,
    expression               varchar(255)         not null,
    environments             varchar(255)         null,
    options                  text                 null,
    log_filename             varchar(255)         null,
    even_in_maintenance_mode tinyint(1) default 0 not null,
    without_overlapping      tinyint(1) default 0 not null,
    on_one_server            tinyint(1) default 0 not null,
    webhook_before           varchar(255)         null,
    webhook_after            varchar(255)         null,
    email_output             varchar(255)         null,
    sendmail_error           tinyint(1) default 0 not null,
    log_success              tinyint(1) default 1 not null,
    log_error                tinyint(1) default 1 not null,
    status                   tinyint(1) default 1 not null,
    run_in_background        tinyint(1) default 0 not null,
    `groups`                 varchar(255)         null,
    created_at               timestamp            null,
    updated_at               timestamp            null,
    deleted_at               timestamp            null,
    sendmail_success         tinyint(1) default 0 not null
)
    collate = utf8mb3_unicode_ci;

