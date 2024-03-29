create table if not exists user_studies
(
    id                            varchar(80)                           not null comment 'Study id which should match OAuth client id',
    type                          varchar(20)                           not null comment 'The type of study may be population, individual, or cohort study',
    cause_variable_id             int unsigned                          not null comment 'variable ID of the cause variable for which the user desires user_variable_relationships',
    effect_variable_id            int unsigned                          not null comment 'variable ID of the effect variable for which the user desires user_variable_relationships',
    correlation_id                int unsigned                          null comment 'ID of the correlation statistics',
    user_id                       bigint unsigned                       not null,
    created_at                    timestamp   default CURRENT_TIMESTAMP not null,
    deleted_at                    timestamp                             null,
    analysis_parameters           text                                  null comment 'Additional parameters for the study such as experiment_end_time, experiment_start_time, cause_variable_filling_value, effect_variable_filling_value',
    user_study_text               longtext                              null comment 'Overrides auto-generated study text',
    user_title                    text                                  null,
    study_status                  varchar(20) default 'publish'         not null,
    comment_status                varchar(20) default 'open'            not null,
    study_password                varchar(20)                           null,
    study_images                  text                                  null comment 'Provided images will override the auto-generated images',
    updated_at                    timestamp   default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    client_id                     varchar(255)                          null,
    published_at                  timestamp                             null,
    newest_data_at                timestamp                             null,
    analysis_requested_at         timestamp                             null,
    reason_for_analysis           varchar(255)                          null,
    analysis_ended_at             timestamp                             null,
    analysis_started_at           timestamp                             null,
    internal_error_message        varchar(255)                          null,
    user_error_message            varchar(255)                          null,
    status                        varchar(25)                           null,
    analysis_settings_modified_at timestamp                             null,
    is_public                     tinyint(1)                            null,
    sort_order                    int                                   not null,
    slug                          varchar(200)                          null comment 'The slug is the part of a URL that identifies a page in human-readable keywords.',
    primary key (id),
    constraint user_cause_effect_type
        unique (user_id, cause_variable_id, effect_variable_id, type),
    constraint user_studies_slug_uindex
        unique (slug),
    constraint user_studies_cause_variable_id_variables_id_fk
        foreign key (cause_variable_id) references global_variables (id),
    constraint user_studies_client_id_fk
        foreign key (client_id) references oa_clients (client_id),
    constraint user_studies_effect_variable_id_variables_id_fk
        foreign key (effect_variable_id) references global_variables (id),
    constraint user_studies_user_id_fk
        foreign key (user_id) references users (id)
)
    comment 'Stores User Study Settings' charset = utf8;

create index cause_variable_id
    on user_studies (cause_variable_id);

create index effect_variable_id
    on user_studies (effect_variable_id);

