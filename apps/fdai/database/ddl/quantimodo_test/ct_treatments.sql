create table quantimodo_test.ct_treatments
(
    id                     int auto_increment
        primary key,
    name                   varchar(100)                        not null,
    variable_id            int unsigned                        not null,
    updated_at             timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    created_at             timestamp default CURRENT_TIMESTAMP not null,
    deleted_at             timestamp                           null,
    number_of_conditions   int unsigned                        null,
    number_of_side_effects int unsigned                        not null,
    constraint treName
        unique (name),
    constraint ct_treatments_variables_id_fk
        foreign key (variable_id) references quantimodo_test.variables (id)
)
    charset = utf8mb3;

