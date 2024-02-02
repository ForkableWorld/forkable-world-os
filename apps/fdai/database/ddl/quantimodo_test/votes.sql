create table quantimodo_test.votes
(
    id                       int auto_increment
        primary key,
    client_id                varchar(80)                         null,
    user_id                  bigint unsigned                     not null,
    value                    int                                 not null comment 'Value of Vote',
    created_at               timestamp default CURRENT_TIMESTAMP not null,
    updated_at               timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deleted_at               timestamp                           null,
    cause_variable_id        int unsigned                        not null,
    effect_variable_id       int unsigned                        not null,
    correlation_id           int                                 null,
    global_variable_relationship_id int                                 null,
    is_public                tinyint(1)                          null,
    constraint votes_user_id_cause_variable_id_effect_variable_id_uindex
        unique (user_id, cause_variable_id, effect_variable_id),
    constraint votes_global_variable_relationships_id_fk
        foreign key (global_variable_relationship_id) references quantimodo_test.global_variable_relationships (id)
            on delete set null,
    constraint votes_cause_variable_id_fk
        foreign key (cause_variable_id) references quantimodo_test.variables (id),
    constraint votes_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint votes_correlations_id_fk
        foreign key (correlation_id) references quantimodo_test.correlations (id),
    constraint votes_effect_variable_id_fk_2
        foreign key (effect_variable_id) references quantimodo_test.variables (id),
    constraint votes_user_id_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID)
)
    charset = utf8mb3;

create index votes_cause_variable_id_index
    on quantimodo_test.votes (cause_variable_id);

create index votes_effect_variable_id_index
    on quantimodo_test.votes (effect_variable_id);

