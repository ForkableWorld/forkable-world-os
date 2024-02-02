create table quantimodo_test.tracker_sessions
(
    id         bigint unsigned auto_increment
        primary key,
    uuid       varchar(255)                        not null,
    user_id    bigint unsigned                     not null,
    device_id  bigint unsigned                     null,
    agent_id   bigint unsigned                     null,
    client_ip  varchar(255)                        not null,
    referer_id bigint unsigned                     null,
    cookie_id  bigint unsigned                     null,
    geoip_id   bigint unsigned                     null,
    is_robot   tinyint(1)                          not null,
    created_at timestamp default CURRENT_TIMESTAMP not null,
    updated_at timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deleted_at timestamp                           null,
    client_id  varchar(255)                        null,
    constraint tracker_sessions_uuid_unique
        unique (uuid),
    constraint tracker_sessions_agent_id_foreign
        foreign key (agent_id) references quantimodo_test.tracker_agents (id)
            on update cascade on delete cascade,
    constraint tracker_sessions_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint tracker_sessions_cookie_id_foreign
        foreign key (cookie_id) references quantimodo_test.tracker_cookies (id)
            on update cascade on delete cascade,
    constraint tracker_sessions_device_id_foreign
        foreign key (device_id) references quantimodo_test.tracker_devices (id)
            on update cascade on delete cascade,
    constraint tracker_sessions_geoip_id_foreign
        foreign key (geoip_id) references quantimodo_test.tracker_geoip (id)
            on update cascade on delete cascade,
    constraint tracker_sessions_referer_id_foreign
        foreign key (referer_id) references quantimodo_test.tracker_referers (id)
            on update cascade on delete cascade,
    constraint tracker_sessions_user_id_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID)
)
    charset = utf8mb3;

create index tracker_sessions_agent_id_index
    on quantimodo_test.tracker_sessions (agent_id);

create index tracker_sessions_client_ip_index
    on quantimodo_test.tracker_sessions (client_ip);

create index tracker_sessions_cookie_id_index
    on quantimodo_test.tracker_sessions (cookie_id);

create index tracker_sessions_created_at_index
    on quantimodo_test.tracker_sessions (created_at);

create index tracker_sessions_device_id_index
    on quantimodo_test.tracker_sessions (device_id);

create index tracker_sessions_geoip_id_index
    on quantimodo_test.tracker_sessions (geoip_id);

create index tracker_sessions_referer_id_index
    on quantimodo_test.tracker_sessions (referer_id);

create index tracker_sessions_updated_at_index
    on quantimodo_test.tracker_sessions (updated_at);

create index tracker_sessions_user_id_index
    on quantimodo_test.tracker_sessions (user_id);

