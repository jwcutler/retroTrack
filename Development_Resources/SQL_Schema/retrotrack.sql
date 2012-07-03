/*
retroTrack Primary DB Schema File
*/

CREATE TABLE satellites (
    id int AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    description text,
    created_on datetime NOT NULL,
    updated_on datetime NOT NULL,
    PRIMARY KEY(`id`)
);

CREATE TABLE groups (
    id int AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    title varchar(255) NOT NULL,
    created_on datetime NOT NULL,
    updated_on datetime NOT NULL,
    PRIMARY KEY(`id`)
);

CREATE TABLE groups_satellites (
    id int AUTO_INCREMENT,
    satellite_id int NOT NULL,
    group_id int NOT NULL,
    PRIMARY KEY(`id`)
);

CREATE TABLE ground_stations (
    id int AUTO_INCREMENT,
    longitude text NOT NULL,
    latitude text NOT NULL,
    created_on datetime NOT NULL,
    updated_on datetime NOT NULL,
    PRIMARY KEY(`id`)
);

CREATE TABLE tles (
    id int AUTO_INCREMENT,
    name varchar(50) NOT NULL,
    satellite_number int NOT NULL,
    classification char(1) NOT NULL,
    launch_year int NOT NULL,
    launch_number int NOT NULL,
    launch_piece varchar(3) NOT NULL,
    epoch_year int NOT NULL,
    epoch varchar(50) NOT NULL,
    ftd_mm_d2 varchar(50) NOT NULL,
    std_mm_d6 varchar(50) NOT NULL,
    bstar_drag varchar(50) NOT NULL,
    element_number int NOT NULL,
    checksum_l1 int NOT NULL,
    inclination varchar(50) NOT NULL,
    right_ascension varchar(50) NOT NULL,
    eccentricity varchar(50) NOT NULL,
    perigee varchar(50) NOT NULL,
    mean_anomaly varchar(50) NOT NULL,
    mean_motion varchar(50) NOT NULL,
    revs int NOT NULL,
    checksum_l2 int NOT NULL,
    created_on datetime NOT NULL,
    last_updated datetime NOT NULL,
    PRIMARY KEY(`id`)
);

CREATE TABLE admins (
    id int AUTO_INCREMENT,
    username VARCHAR(50),
    password VARCHAR(50),
    created_on datetime NOT NULL,
    last_updated datetime NOT NULL,
    PRIMARY KEY(`id`)
);
