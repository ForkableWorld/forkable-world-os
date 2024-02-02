create table quantimodo_test.correlation_usefulness_votes
(
    id                       int unsigned auto_increment
        primary key,
    cause_variable_id        int unsigned                        not null,
    effect_variable_id       int unsigned                        not null,
    correlation_id           int                                 null,
    global_variable_relationship_id int                                 null,
    user_id                  bigint unsigned                     not null,
    vote                     int                                 not null comment 'The opinion of the data owner on whether or not knowledge of this
                    relationship is useful in helping them improve an outcome of interest.
                    -1 corresponds to a down vote. 1 corresponds to an up vote. 0 corresponds to removal of a
                    previous vote.  null corresponds to never having voted before.',
    created_at               timestamp default CURRENT_TIMESTAMP not null,
    updated_at               timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deleted_at               timestamp                           null,
    client_id                varchar(80) charset utf8mb3         null,
    is_public                tinyint(1)                          null,
    constraint correlation_usefulness_votes_user_cause_effect_uindex
        unique (user_id, cause_variable_id, effect_variable_id),
    constraint correlation_usefulness_votes_global_variable_relationships_id_fk
        foreign key (global_variable_relationship_id) references quantimodo_test.global_variable_relationships (id),
    constraint correlation_usefulness_votes_cause_variables_id_fk
        foreign key (cause_variable_id) references quantimodo_test.variables (id),
    constraint correlation_usefulness_votes_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint correlation_usefulness_votes_correlations_id_fk
        foreign key (correlation_id) references quantimodo_test.correlations (id),
    constraint correlation_usefulness_votes_effect_variables_id_fk
        foreign key (effect_variable_id) references quantimodo_test.variables (id),
    constraint correlation_usefulness_votes_wp_users_ID_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID)
)
    comment 'The opinion of the data owner on whether or not knowledge of this
                relationship is useful in helping them improve an outcome of interest.
                -1 corresponds to a down vote. 1 corresponds to an up vote. 0 corresponds to removal of a
                previous vote.  null corresponds to never having voted before.' charset = latin1;

