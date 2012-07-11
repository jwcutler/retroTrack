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
    description text,
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

CREATE TABLE stations (
    id int AUTO_INCREMENT,
    longitude text NOT NULL,
    latitude text NOT NULL,
    name varchar(50) NOT NULL,
    description text,
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
    raw_l1 text NOT NULL,
    raw_l2 text NOT NULL,
    created_on datetime NOT NULL,
    PRIMARY KEY(`id`)
);

CREATE TABLE admins (
    id int AUTO_INCREMENT,
    username VARCHAR(50),
    password VARCHAR(50),
    PRIMARY KEY(`id`)
);

CREATE TABLE configurations (
    id int AUTO_INCREMENT,
    name varchar(50) NOT NULL,
    value text,
    PRIMARY KEY (`id`)
);

/*
Load in initial data.
*/
INSERT INTO `admins` (`id`, `username`, `password`) VALUES (1, 'admin', '194b948cb2a8834e39f540514cb7ce430efdba60');
INSERT INTO `configurations` (`name`, `value`) VALUES ('tle_last_update', '0');
INSERT INTO `configurations` (`name`, `value`) VALUES ('map_file', 'map_bg.jpg');
INSERT INTO `configurations` (`name`, `value`) VALUES ('clock_update_period', '1000');
INSERT INTO `configurations` (`name`, `value`) VALUES ('map_update_period', '5000');
INSERT INTO `configurations` (`name`, `value`) VALUES ('default_ground_station', '0');
INSERT INTO `configurations` (`name`, `value`) VALUES ('show_grid', '1');
INSERT INTO `configurations` (`name`, `value`) VALUES ('show_sun', '1');
INSERT INTO `configurations` (`name`, `value`) VALUES ('show_satellite_names', '1');
INSERT INTO `configurations` (`name`, `value`) VALUES ('satellite_color', 'FFFFFF');
INSERT INTO `configurations` (`name`, `value`) VALUES ('satellite_selected_color', 'FFFF00');
INSERT INTO `configurations` (`name`, `value`) VALUES ('satellite_label_color', 'FFFFFF');
INSERT INTO `configurations` (`name`, `value`) VALUES ('sun_color', 'FFFF00');
INSERT INTO `configurations` (`name`, `value`) VALUES ('grid_color', 'A5A2A2');
INSERT INTO `configurations` (`name`, `value`) VALUES ('ground_station_color', 'FF0000');
INSERT INTO `configurations` (`name`, `value`) VALUES ('eclipse_color', 'DDDDDD');
INSERT INTO `configurations` (`name`, `value`) VALUES ('satellite_size', '6');
