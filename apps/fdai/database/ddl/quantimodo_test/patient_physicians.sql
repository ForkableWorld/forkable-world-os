create table quantimodo_test.patient_physicians
(
    id                int unsigned auto_increment
        primary key,
    patient_user_id   bigint unsigned                     not null comment 'The patient who has granted data access to the physician. ',
    physician_user_id bigint unsigned                     not null comment 'The physician who has been granted access to the patients data.',
    scopes            varchar(2000)                       not null comment 'Whether the physician has read access and/or write access to the data.',
    created_at        timestamp default CURRENT_TIMESTAMP not null,
    updated_at        timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deleted_at        timestamp                           null,
    constraint patients_patient_user_id_physician_user_id_uindex
        unique (patient_user_id, physician_user_id),
    constraint patient_physicians_wp_users_ID_fk
        foreign key (patient_user_id) references quantimodo_test.wp_users (ID),
    constraint patient_physicians_wp_users_ID_fk_2
        foreign key (physician_user_id) references quantimodo_test.wp_users (ID)
)
    charset = latin1;

