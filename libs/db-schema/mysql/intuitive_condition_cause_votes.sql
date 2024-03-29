create table if not exists intuitive_condition_cause_votes
(
    id                    int auto_increment
        primary key,
    condition_id          int                                 not null,
    cause_id              int                                 not null,
    condition_variable_id int unsigned                        not null,
    cause_variable_id     int unsigned                        not null,
    votes_percent         int                                 not null,
    updated_at            timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    created_at            timestamp default CURRENT_TIMESTAMP not null,
    deleted_at            timestamp                           null,
    constraint intuitive_condition_cause_votes_cause_id_condition_id_uindex
        unique (cause_id, condition_id),
    constraint intuitive_condition_cause_votes_cause_uindex
        unique (cause_variable_id, condition_variable_id),
    constraint intuitive_condition_cause_votes_ct_causes_cause_fk
        foreign key (cause_id) references intuitive_causes_of_illness (id),
    constraint intuitive_condition_cause_votes_ct_conditions_id_condition_fk
        foreign key (condition_id) references intuitive_conditions (id),
    constraint intuitive_condition_cause_votes_variables_id_condition_fk
        foreign key (condition_variable_id) references global_variables (id),
    constraint intuitive_condition_cause_votes_variables_id_fk
        foreign key (cause_variable_id) references global_variables (id)
)
    comment 'User self-reported conditions and causes' charset = utf8;

