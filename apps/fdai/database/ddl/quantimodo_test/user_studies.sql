create table quantimodo_test.user_studies
(
    id                            varchar(80)                           not null comment 'Study id which should match OAuth client id'
        primary key,
    cause_variable_id             int unsigned                          not null comment 'variable ID of the cause variable for which the user desires user_variable_relationships',
    effect_variable_id            int unsigned                          not null comment 'variable ID of the effect variable for which the user desires user_variable_relationships',
    cause_user_variable_id        int unsigned                          not null comment 'variable ID of the cause variable for which the user desires user_variable_relationships',
    effect_user_variable_id       int unsigned                          not null comment 'variable ID of the effect variable for which the user desires user_variable_relationships',
    correlation_id                int                                   null comment 'ID of the correlation statistics',
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
    wp_post_id                    int                                   null,
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
    constraint user_studies_correlation_id_uindex
        unique (correlation_id),
    constraint user_studies_slug_uindex
        unique (slug),
    constraint user_studies_user_cause_effect
        unique (user_id, cause_variable_id, effect_variable_id),
    constraint user_studies_cause_user_variables_id_fk
        foreign key (cause_user_variable_id) references quantimodo_test.user_variables (id),
    constraint user_studies_cause_variables_id_fk
        foreign key (cause_variable_id) references quantimodo_test.variables (id),
    constraint user_studies_correlations_id_fk
        foreign key (correlation_id) references quantimodo_test.correlations (id),
    constraint user_studies_effect_user_variables_id_fk
        foreign key (effect_user_variable_id) references quantimodo_test.user_variables (id),
    constraint user_studies_effect_variables_id_fk
        foreign key (effect_variable_id) references quantimodo_test.variables (id),
    constraint user_studies_user_id_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID)
)
    comment 'Stores Study Settings' collate = utf8mb4_bin;

