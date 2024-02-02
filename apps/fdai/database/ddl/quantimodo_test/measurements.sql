create table quantimodo_test.measurements
(
    id                   bigint auto_increment
        primary key,
    user_id              bigint unsigned                         not null,
    client_id            varchar(80)                             null,
    connector_id         int unsigned                            null comment 'The id for the connector data source from which the measurement was obtained',
    variable_id          int unsigned                            not null comment 'ID of the variable for which we are creating the measurement records',
    start_time           int unsigned                            not null comment 'Start time for the measurement event in ISO 8601',
    value                double                                  not null comment 'The value of the measurement after conversion to the default unit for that variable',
    unit_id              smallint unsigned                       not null comment 'The default unit for the variable',
    original_value       double                                  not null comment 'Value of measurement as originally posted (before conversion to default unit)',
    original_unit_id     smallint unsigned                       not null comment 'Unit id of measurement as originally submitted',
    duration             int                                     null comment 'Duration of the event being measurement in seconds',
    note                 text                                    null comment 'An optional note the user may include with their measurement',
    latitude             double                                  null comment 'Latitude at which the measurement was taken',
    longitude            double                                  null comment 'Longitude at which the measurement was taken',
    location             varchar(255)                            null comment 'location',
    created_at           timestamp default '0000-00-00 00:00:00' not null comment 'Time at which this measurement was originally stored',
    updated_at           timestamp default CURRENT_TIMESTAMP     not null on update CURRENT_TIMESTAMP comment 'Time at which this measurement was last updated',
    error                text                                    null comment 'An error message if there is a problem with the measurement',
    variable_category_id tinyint unsigned                        not null comment 'Variable category ID',
    deleted_at           datetime                                null,
    source_name          varchar(80)                             null comment 'Name of the application or device',
    user_variable_id     int unsigned                            not null,
    start_at             timestamp default '0000-00-00 00:00:00' not null,
    connection_id        int unsigned                            null,
    connector_import_id  int unsigned                            null,
    deletion_reason      varchar(280)                            null comment 'The reason the variable was deleted.',
    original_start_at    timestamp default '0000-00-00 00:00:00' not null,
    constraint measurements_pk
        unique (user_id, variable_id, start_time),
    constraint measurements_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint measurements_connections_id_fk
        foreign key (connection_id) references quantimodo_test.connections (id),
    constraint measurements_connector_imports_id_fk
        foreign key (connector_import_id) references quantimodo_test.connector_imports (id),
    constraint measurements_connectors_id_fk
        foreign key (connector_id) references quantimodo_test.connectors (id),
    constraint measurements_original_unit_id_fk
        foreign key (original_unit_id) references quantimodo_test.units (id),
    constraint measurements_unit_id_fk
        foreign key (unit_id) references quantimodo_test.units (id),
    constraint measurements_user_id_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID),
    constraint measurements_user_variables_user_variable_id_fk
        foreign key (user_variable_id) references quantimodo_test.user_variables (id),
    constraint measurements_variable_category_id_fk
        foreign key (variable_category_id) references quantimodo_test.variable_categories (id),
    constraint measurements_variables_id_fk
        foreign key (variable_id) references quantimodo_test.variables (id)
)
    comment 'Measurements are any value that can be recorded like daily steps, a mood rating, or apples eaten.'
    charset = utf8mb3;

create index fk_measurementUnits
    on quantimodo_test.measurements (unit_id);

create index measurements_start_time_index
    on quantimodo_test.measurements (start_time);

create index measurements_user_id_variable_category_id_start_time_index
    on quantimodo_test.measurements (user_id, variable_category_id, start_time);

create index measurements_user_variables_variable_id_user_id_fk
    on quantimodo_test.measurements (variable_id, user_id);

create index measurements_variable_id_start_time_index
    on quantimodo_test.measurements (variable_id, start_time);

create index measurements_variable_id_value_start_time_index
    on quantimodo_test.measurements (variable_id, value, start_time);

