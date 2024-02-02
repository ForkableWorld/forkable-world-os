create table quantimodo_test.connector_imports
(
    id                           int unsigned auto_increment
        primary key,
    client_id                    varchar(80)                            null,
    connection_id                int unsigned                           null,
    connector_id                 int unsigned                           not null,
    created_at                   timestamp    default CURRENT_TIMESTAMP not null,
    deleted_at                   timestamp                              null,
    earliest_measurement_at      timestamp                              null,
    import_ended_at              timestamp                              null,
    import_started_at            timestamp                              null,
    internal_error_message       text                                   null,
    latest_measurement_at        timestamp                              null,
    number_of_measurements       int unsigned default '0'               not null,
    reason_for_import            varchar(255)                           null,
    success                      tinyint(1)   default 1                 null,
    updated_at                   timestamp    default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    user_error_message           text                                   null,
    user_id                      bigint unsigned                        not null,
    additional_meta_data         json                                   null,
    number_of_connector_requests int unsigned                           null comment 'Number of Connector Requests for this Connector Import.
                [Formula:
                    update connector_imports
                        left join (
                            select count(id) as total, connector_import_id
                            from connector_requests
                            group by connector_import_id
                        )
                        as grouped on connector_imports.id = grouped.connector_import_id
                    set connector_imports.number_of_connector_requests = count(grouped.total)
                ]
                ',
    imported_data_from_at        timestamp                              null comment 'Earliest data that we''ve requested from this data source ',
    imported_data_end_at         timestamp                              null comment 'Most recent data that we''ve requested from this data source ',
    credentials                  text                                   null comment 'Encrypted user credentials for accessing third party data',
    connector_requests           timestamp                              null comment 'Most recent data that we''ve requested from this data source ',
    constraint connector_imports_connection_id_created_at_uindex
        unique (connection_id, created_at),
    constraint connector_imports_connector_id_user_id_created_at_uindex
        unique (connector_id, user_id, created_at),
    constraint connector_imports_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint connector_imports_connections_id_fk
        foreign key (connection_id) references quantimodo_test.connections (id),
    constraint connector_imports_connectors_id_fk
        foreign key (connector_id) references quantimodo_test.connectors (id),
    constraint connector_imports_wp_users_ID_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID)
)
    comment 'Individual imports from a given data source for a user.' charset = utf8mb3;

create index IDX_connector_imports_user_connector
    on quantimodo_test.connector_imports (user_id, connector_id);

