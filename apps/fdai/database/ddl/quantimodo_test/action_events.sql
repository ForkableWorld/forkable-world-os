create table quantimodo_test.action_events
(
    id              bigint unsigned auto_increment
        primary key,
    batch_id        char(36)                      not null,
    user_id         bigint unsigned               not null,
    name            varchar(255)                  not null,
    actionable_type varchar(255)                  not null,
    actionable_id   bigint unsigned               not null,
    target_type     varchar(255)                  not null,
    target_id       bigint unsigned               not null,
    model_type      varchar(255)                  not null,
    model_id        bigint unsigned               null,
    fields          text                          not null,
    status          varchar(25) default 'running' not null,
    exception       text                          not null,
    created_at      timestamp                     null,
    updated_at      timestamp                     null,
    original        text                          null,
    changes         text                          null
)
    collate = utf8mb3_unicode_ci;

create index action_events_actionable_type_actionable_id_index
    on quantimodo_test.action_events (actionable_type, actionable_id);

create index action_events_batch_id_model_type_model_id_index
    on quantimodo_test.action_events (batch_id, model_type, model_id);

create index action_events_user_id_index
    on quantimodo_test.action_events (user_id);

