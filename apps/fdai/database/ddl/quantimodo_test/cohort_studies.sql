create table quantimodo_test.cohort_studies
(
    id                            int                                   not null comment 'Unique ID for the cohort study'
        primary key,
    cohort_study_statistics_id    int                                   null comment 'ID of associated analytical results',
    cause_variable_id             int unsigned                          not null comment 'Variable ID of the predictor variable',
    effect_variable_id            int unsigned                          not null comment 'Variable ID of the outcome variable',
    user_id                       bigint unsigned                       not null comment 'User ID of the principal investigator for the study',
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
    wp_post_id                    int                                   null,
    cohort_correlation_id         int                                   null,
    newest_data_at                timestamp                             null,
    analysis_requested_at         timestamp                             null,
    reason_for_analysis           varchar(255)                          null,
    analysis_ended_at             timestamp                             null,
    analysis_started_at           timestamp                             null,
    internal_error_message        varchar(255)                          null,
    user_error_message            varchar(255)                          null,
    status                        varchar(25)                           null,
    analysis_settings_modified_at timestamp                             null,
    is_public                     tinyint(1)                            not null comment 'Indicates whether the study is private or should be publicly displayed.',
    sort_order                    int                                   not null,
    slug                          varchar(200)                          null comment 'The slug is the part of a URL that identifies a page in human-readable keywords.',
    constraint cohort_studies_slug_uindex
        unique (slug),
    constraint user_cause_effect
        unique (user_id, cause_variable_id, effect_variable_id),
    constraint cohort_studies_cause_variable_id_variables_id_fk
        foreign key (cause_variable_id) references quantimodo_test.variables (id),
    constraint cohort_studies_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint cohort_studies_effect_variable_id_variables_id_fk
        foreign key (effect_variable_id) references quantimodo_test.variables (id),
    constraint cohort_studies_user_id_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID)
)
    comment 'Cohort Study Analysis Settings and Written Text' charset = utf8mb3;

create index cause_variable_id
    on quantimodo_test.cohort_studies (cause_variable_id);

create index cohort_studies_cause_variable_id
    on quantimodo_test.cohort_studies (cause_variable_id);

create index cohort_studies_effect_variable_id
    on quantimodo_test.cohort_studies (effect_variable_id);

create index effect_variable_id
    on quantimodo_test.cohort_studies (effect_variable_id);

