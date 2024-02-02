create table quantimodo_test.user_tags
(
    id                      int unsigned auto_increment
        primary key,
    tagged_variable_id      int unsigned                        not null comment 'This is the id of the variable being tagged with an ingredient or something.',
    tag_variable_id         int unsigned                        not null comment 'This is the id of the ingredient variable whose value is determined based on the value of the tagged variable.',
    conversion_factor       double                              not null comment 'Number by which we multiply the tagged variable''s value to obtain the tag variable''s value',
    user_id                 bigint unsigned                     not null,
    created_at              timestamp default CURRENT_TIMESTAMP not null,
    updated_at              timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    client_id               varchar(80)                         null,
    deleted_at              timestamp                           null,
    tagged_user_variable_id int unsigned                        null,
    tag_user_variable_id    int unsigned                        null,
    constraint UK_user_tag_tagged
        unique (tagged_variable_id, tag_variable_id, user_id),
    constraint user_tags_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint user_tags_tag_user_variable_id_fk
        foreign key (tag_user_variable_id) references quantimodo_test.user_variables (id),
    constraint user_tags_tag_variable_id_variables_id_fk
        foreign key (tag_variable_id) references quantimodo_test.variables (id),
    constraint user_tags_tagged_user_variable_id_fk
        foreign key (tagged_user_variable_id) references quantimodo_test.user_variables (id),
    constraint user_tags_tagged_variable_id_variables_id_fk
        foreign key (tagged_variable_id) references quantimodo_test.variables (id),
    constraint user_tags_user_id_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID)
)
    charset = utf8mb3;

create index fk_conversionUnit
    on quantimodo_test.user_tags (tag_variable_id);

