create table quantimodo_test.tracker_log
(
    id            bigint unsigned auto_increment
        primary key,
    session_id    bigint unsigned                     null,
    path_id       bigint unsigned                     null,
    query_id      bigint unsigned                     null,
    method        varchar(10)                         not null,
    route_path_id bigint unsigned                     null,
    is_ajax       tinyint(1)                          not null,
    is_secure     tinyint(1)                          not null,
    is_json       tinyint(1)                          not null,
    wants_json    tinyint(1)                          not null,
    error_id      bigint unsigned                     null,
    created_at    timestamp default CURRENT_TIMESTAMP not null,
    updated_at    timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    client_id     varchar(255)                        null,
    user_id       bigint unsigned                     not null,
    deleted_at    timestamp                           null,
    constraint tracker_log_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint tracker_log_error_id_foreign
        foreign key (error_id) references quantimodo_test.tracker_errors (id)
            on update cascade on delete cascade,
    constraint tracker_log_path_id_foreign
        foreign key (path_id) references quantimodo_test.tracker_paths (id)
            on update cascade on delete cascade,
    constraint tracker_log_query_id_foreign
        foreign key (query_id) references quantimodo_test.tracker_queries (id)
            on update cascade on delete cascade,
    constraint tracker_log_route_path_id_foreign
        foreign key (route_path_id) references quantimodo_test.tracker_route_paths (id)
            on update cascade on delete cascade,
    constraint tracker_log_user_id_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID)
)
    charset = utf8mb3;

create index tracker_log_created_at_index
    on quantimodo_test.tracker_log (created_at);

create index tracker_log_error_id_index
    on quantimodo_test.tracker_log (error_id);

create index tracker_log_method_index
    on quantimodo_test.tracker_log (method);

create index tracker_log_path_id_index
    on quantimodo_test.tracker_log (path_id);

create index tracker_log_query_id_index
    on quantimodo_test.tracker_log (query_id);

create index tracker_log_route_path_id_index
    on quantimodo_test.tracker_log (route_path_id);

create index tracker_log_session_id_index
    on quantimodo_test.tracker_log (session_id);

create index tracker_log_updated_at_index
    on quantimodo_test.tracker_log (updated_at);

