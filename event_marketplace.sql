-- Event Marketplace Database Schema
-- Updated: 2026-05-10
-- Compatible with: MariaDB 10.x / MySQL 5.7+
-- PHP Version: 8.1+

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Database: `event_marketplace`
-- --------------------------------------------------------

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','vendor','admin') NOT NULL,
  `password_reset_token` varchar(128) DEFAULT NULL,
  `password_reset_expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `role`) VALUES
(3, 'Mark Pearson', 'm.pearson1', 'markyj@zoho.com', '$2y$10$i7T5IbGkzDuPTrHvstBG5OeaFHRTbHdMDGloA8N049zWjZIoEc8Ze', 'vendor'),
(4, 'mark90', 'mark90', 'markjpearson@me.com', '$2y$10$i7T5IbGkzDuPTrHvstBG5OeaFHRTbHdMDGloA8N049zWjZIoEc8Ze', 'customer')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `password`=VALUES(`password`), `role`=VALUES(`role`);

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `role`) VALUES
(1, 'Site Admin', 'admin', 'admin@example.test', '$2y$10$i7T5IbGkzDuPTrHvstBG5OeaFHRTbHdMDGloA8N049zWjZIoEc8Ze', 'admin'),
(6, 'QA Customer', 'qa_customer', 'qa.customer@example.test', '$2y$10$i7T5IbGkzDuPTrHvstBG5OeaFHRTbHdMDGloA8N049zWjZIoEc8Ze', 'customer')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `username`=VALUES(`username`), `email`=VALUES(`email`), `password`=VALUES(`password`), `role`=VALUES(`role`);

-- --------------------------------------------------------
-- Table: categories
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_categories_parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categories` (`id`, `parent_id`, `name`) VALUES
(1, NULL, 'Catering & Drinks'),
(2, NULL, 'Cakes & Desserts'),
(3, NULL, 'Photography & Videography'),
(4, NULL, 'Music & DJs'),
(5, NULL, 'Entertainment'),
(6, NULL, 'Children’s Parties'),
(7, NULL, 'Decorations & Styling'),
(8, NULL, 'Flowers & Plants'),
(9, NULL, 'Furniture & Equipment Hire'),
(10, NULL, 'Lighting & Special Effects'),
(11, NULL, 'Photo Booths & Experiences'),
(12, NULL, 'Transport'),
(13, NULL, 'Beauty & Personal Care'),
(14, NULL, 'Stationery & Printing'),
(15, NULL, 'Gifts & Favours'),
(16, NULL, 'Event Planning & Support'),
(17, NULL, 'Marquees & Outdoor Events'),
(18, NULL, 'Audio Visual & Production'),
(19, NULL, 'Venue Hire'),
(20, NULL, 'Activities & Experiences'),
(21, NULL, 'Staffing & Security'),
(22, NULL, 'Accommodation & Travel Support'),
(23, NULL, 'Other Services'),
(100, 1, 'Full-service catering'),
(101, 1, 'Mobile food vendors'),
(102, 1, 'Outdoor & casual catering'),
(103, 1, 'Drinks & bars'),
(104, 1, 'Specialist food'),
(105, 2, 'Cakes'),
(106, 2, 'Dessert services'),
(107, 2, 'Personalised treats'),
(108, 3, 'Photography'),
(109, 3, 'Videography'),
(110, 3, 'Specialist imaging'),
(111, 4, 'DJs'),
(112, 4, 'Live music'),
(113, 4, 'Specialist music'),
(114, 5, 'Performers'),
(115, 5, 'Interactive entertainment'),
(116, 5, 'Shows & acts'),
(117, 6, 'Children’s entertainers'),
(118, 6, 'Activities'),
(119, 6, 'Play hire'),
(120, 7, 'Venue styling'),
(121, 7, 'Decor hire'),
(122, 7, 'Signage & statement pieces'),
(123, 8, 'Floristry'),
(124, 8, 'Floral installations'),
(125, 8, 'Plant hire'),
(126, 9, 'Furniture'),
(127, 9, 'Tableware'),
(128, 9, 'Event equipment'),
(129, 10, 'Lighting'),
(130, 10, 'Dance floors'),
(131, 10, 'Special effects'),
(132, 11, 'Photo booths'),
(133, 11, 'Video booths'),
(134, 11, 'Guest experiences'),
(135, 12, 'Wedding transport'),
(136, 12, 'Guest transport'),
(137, 12, 'Specialist transport'),
(138, 13, 'Makeup'),
(139, 13, 'Hair'),
(140, 13, 'Wellbeing'),
(141, 14, 'Invitations'),
(142, 14, 'On-the-day stationery'),
(143, 14, 'Printed materials'),
(144, 15, 'Guest favours'),
(145, 15, 'Personalised gifts'),
(146, 15, 'Corporate gifts'),
(147, 16, 'Planning'),
(148, 16, 'Operational support'),
(149, 16, 'Admin services'),
(150, 17, 'Structures'),
(151, 17, 'Outdoor setup'),
(152, 17, 'Weather support'),
(153, 18, 'Sound'),
(154, 18, 'Visuals'),
(155, 18, 'Production'),
(156, 19, 'Traditional venues'),
(157, 19, 'Corporate venues'),
(158, 19, 'Unusual venues'),
(159, 20, 'Creative activities'),
(160, 20, 'Competitive activities'),
(161, 20, 'Animal experiences'),
(162, 21, 'Event staff'),
(163, 21, 'Security'),
(164, 21, 'Specialist staff'),
(165, 22, 'Accommodation'),
(166, 22, 'Travel support'),
(167, 23, 'Specialist services'),
(168, 23, 'Digital services'),
(169, 23, 'Bespoke services'),
(1000, 100, 'Wedding catering'),
(1001, 100, 'Corporate catering'),
(1002, 100, 'Private dining'),
(1003, 100, 'Formal plated meals'),
(1004, 100, 'Family-style sharing meals'),
(1005, 100, 'Buffet catering'),
(1006, 100, 'Canapé receptions'),
(1007, 100, 'Funeral catering'),
(1008, 101, 'Food trucks'),
(1009, 101, 'Burger vans'),
(1010, 101, 'Pizza vans'),
(1011, 101, 'Fish and chip vans'),
(1012, 101, 'Taco stalls'),
(1013, 101, 'Greek food stalls'),
(1014, 101, 'Asian street food stalls'),
(1015, 101, 'Caribbean food stalls'),
(1016, 102, 'BBQ catering'),
(1017, 102, 'Hog roast'),
(1018, 102, 'Paella catering'),
(1019, 102, 'Picnic catering'),
(1020, 102, 'Grazing tables'),
(1021, 102, 'Street food stalls'),
(1022, 102, 'Festival catering'),
(1023, 103, 'Mobile bars'),
(1024, 103, 'Cocktail bars'),
(1025, 103, 'Mocktail bars'),
(1026, 103, 'Coffee vans'),
(1027, 103, 'Prosecco vans'),
(1028, 103, 'Gin bars'),
(1029, 103, 'Beer bars'),
(1030, 103, 'Champagne service'),
(1031, 104, 'Afternoon tea'),
(1032, 104, 'Breakfast catering'),
(1033, 104, 'Brunch catering'),
(1034, 104, 'Dessert tables'),
(1035, 104, 'Sweet carts'),
(1036, 104, 'Ice cream vans'),
(1037, 104, 'Popcorn carts'),
(1038, 104, 'Doughnut walls'),
(1039, 105, 'Wedding cakes'),
(1040, 105, 'Birthday cakes'),
(1041, 105, 'Christening cakes'),
(1042, 105, 'Corporate cakes'),
(1043, 105, 'Cupcake towers'),
(1044, 105, 'Cake pops'),
(1045, 105, 'Tray bakes'),
(1046, 106, 'Dessert tables'),
(1047, 106, 'Macaron towers'),
(1048, 106, 'Chocolate fountains'),
(1049, 106, 'Waffle stations'),
(1050, 106, 'Crepe stations'),
(1051, 106, 'Ice cream stations'),
(1052, 106, 'Sweet buffets'),
(1053, 107, 'Personalised biscuits'),
(1054, 107, 'Branded cupcakes'),
(1055, 107, 'Wedding favours'),
(1056, 107, 'Edible prints'),
(1057, 107, 'Children’s party treats'),
(1058, 108, 'Wedding photography'),
(1059, 108, 'Party photography'),
(1060, 108, 'Corporate photography'),
(1061, 108, 'Event photography'),
(1062, 108, 'Family photography'),
(1063, 108, 'School prom photography'),
(1064, 108, 'Sports event photography'),
(1065, 109, 'Wedding videography'),
(1066, 109, 'Event videography'),
(1067, 109, 'Corporate videography'),
(1068, 109, 'Highlight films'),
(1069, 109, 'Full ceremony filming'),
(1070, 109, 'Social media reels'),
(1071, 109, 'Live streaming'),
(1072, 110, 'Drone photography'),
(1073, 110, 'Drone videography'),
(1074, 110, 'Content creators'),
(1075, 110, 'Same-day edits'),
(1076, 110, 'Photo printing stations'),
(1077, 110, 'Roaming photographers'),
(1078, 111, 'Wedding DJs'),
(1079, 111, 'Party DJs'),
(1080, 111, 'Corporate DJs'),
(1081, 111, 'Silent disco DJs'),
(1082, 111, 'Children’s DJs'),
(1083, 111, 'Club-style DJs'),
(1084, 111, 'Karaoke DJs'),
(1085, 112, 'Function bands'),
(1086, 112, 'Wedding bands'),
(1087, 112, 'Solo singers'),
(1088, 112, 'Acoustic performers'),
(1089, 112, 'String quartets'),
(1090, 112, 'Saxophonists'),
(1091, 112, 'Pianists'),
(1092, 112, 'Harpists'),
(1093, 113, 'Ceilidh bands'),
(1094, 113, 'Tribute acts'),
(1095, 113, 'Choirs'),
(1096, 113, 'Brass bands'),
(1097, 113, 'Mariachi bands'),
(1098, 113, 'Steel bands'),
(1099, 113, 'Opera singers'),
(1100, 114, 'Magicians'),
(1101, 114, 'Comedians'),
(1102, 114, 'Caricaturists'),
(1103, 114, 'Singing waiters'),
(1104, 114, 'Circus performers'),
(1105, 114, 'Fire performers'),
(1106, 114, 'Stilt walkers'),
(1107, 114, 'Living statues'),
(1108, 115, 'Casino tables'),
(1109, 115, 'Race nights'),
(1110, 115, 'Murder mystery events'),
(1111, 115, 'Quiz hosts'),
(1112, 115, 'Game show hosts'),
(1113, 115, 'Bingo hosts'),
(1114, 115, 'Escape room experiences'),
(1115, 116, 'Dancers'),
(1116, 116, 'Drag performers'),
(1117, 116, 'Burlesque performers'),
(1118, 116, 'Tribute shows'),
(1119, 116, 'Theatre performers'),
(1120, 116, 'LED performers'),
(1121, 116, 'Aerial performers'),
(1122, 117, 'Party entertainers'),
(1123, 117, 'Character appearances'),
(1124, 117, 'Mascots'),
(1125, 117, 'Magicians for children'),
(1126, 117, 'Balloon modellers'),
(1127, 117, 'Puppet shows'),
(1128, 117, 'Storytelling sessions'),
(1129, 118, 'Face painting'),
(1130, 118, 'Glitter tattoos'),
(1131, 118, 'Craft parties'),
(1132, 118, 'Slime parties'),
(1133, 118, 'Science parties'),
(1134, 118, 'Lego parties'),
(1135, 118, 'Gaming parties'),
(1136, 118, 'Pamper parties'),
(1137, 119, 'Soft play hire'),
(1138, 119, 'Bouncy castles'),
(1139, 119, 'Inflatable slides'),
(1140, 119, 'Ball pits'),
(1141, 119, 'Toddler play zones'),
(1142, 119, 'Garden games'),
(1143, 119, 'Mini discos'),
(1144, 120, 'Full venue styling'),
(1145, 120, 'Wedding styling'),
(1146, 120, 'Corporate styling'),
(1147, 120, 'Party styling'),
(1148, 120, 'Table styling'),
(1149, 120, 'Ceremony styling'),
(1150, 120, 'Reception styling'),
(1151, 121, 'Backdrops'),
(1152, 121, 'Flower walls'),
(1153, 121, 'Balloon arches'),
(1154, 121, 'Balloon garlands'),
(1155, 121, 'Centrepieces'),
(1156, 121, 'Aisle decor'),
(1157, 121, 'Themed props'),
(1158, 121, 'Sequin walls'),
(1159, 122, 'Welcome signs'),
(1160, 122, 'Table plans'),
(1161, 122, 'Neon signs'),
(1162, 122, 'Acrylic signs'),
(1163, 122, 'Wooden signs'),
(1164, 122, 'Mirror signs'),
(1165, 122, 'Personalised banners'),
(1166, 123, 'Wedding florists'),
(1167, 123, 'Event florists'),
(1168, 123, 'Corporate flowers'),
(1169, 123, 'Funeral flowers'),
(1170, 123, 'Bouquets'),
(1171, 123, 'Buttonholes'),
(1172, 123, 'Floral centrepieces'),
(1173, 124, 'Floral arches'),
(1174, 124, 'Hanging flowers'),
(1175, 124, 'Flower walls'),
(1176, 124, 'Aisle flowers'),
(1177, 124, 'Staircase flowers'),
(1178, 124, 'Table garlands'),
(1179, 125, 'Indoor plant hire'),
(1180, 125, 'Outdoor plant hire'),
(1181, 125, 'Tree hire'),
(1182, 125, 'Living walls'),
(1183, 125, 'Potted plant displays'),
(1184, 126, 'Chair hire'),
(1185, 126, 'Table hire'),
(1186, 126, 'Lounge furniture'),
(1187, 126, 'Outdoor furniture'),
(1188, 126, 'Bar furniture'),
(1189, 126, 'Children’s furniture'),
(1190, 126, 'Rustic furniture'),
(1191, 127, 'Crockery hire'),
(1192, 127, 'Cutlery hire'),
(1193, 127, 'Glassware hire'),
(1194, 127, 'Linen hire'),
(1195, 127, 'Napkin hire'),
(1196, 127, 'Charger plates'),
(1197, 127, 'Serving equipment'),
(1198, 128, 'Gazebo hire'),
(1199, 128, 'Generator hire'),
(1200, 128, 'Heating hire'),
(1201, 128, 'Cooling fans'),
(1202, 128, 'Queue barriers'),
(1203, 128, 'Coat rails'),
(1204, 128, 'Dance barriers'),
(1205, 129, 'Uplighting'),
(1206, 129, 'Festoon lighting'),
(1207, 129, 'Fairy lights'),
(1208, 129, 'Stage lighting'),
(1209, 129, 'Outdoor lighting'),
(1210, 129, 'Mood lighting'),
(1211, 129, 'Moving head lights'),
(1212, 130, 'LED dance floors'),
(1213, 130, 'White dance floors'),
(1214, 130, 'Black dance floors'),
(1215, 130, 'Rustic dance floors'),
(1216, 130, 'Personalised dance floors'),
(1217, 131, 'Cold spark machines'),
(1218, 131, 'Confetti cannons'),
(1219, 131, 'Smoke machines'),
(1220, 131, 'Dry ice effects'),
(1221, 131, 'CO2 jets'),
(1222, 131, 'Bubble machines'),
(1223, 131, 'Snow machines'),
(1224, 132, 'Classic photo booths'),
(1225, 132, 'Open-air photo booths'),
(1226, 132, 'Magic mirror booths'),
(1227, 132, 'Selfie pods'),
(1228, 132, 'Roaming photo booths'),
(1229, 132, 'Green screen booths'),
(1230, 133, '360 video booths'),
(1231, 133, 'Video guest books'),
(1232, 133, 'Slow-motion booths'),
(1233, 133, 'Confessional booths'),
(1234, 133, 'TikTok-style booths'),
(1235, 134, 'Audio guest books'),
(1236, 134, 'Polaroid stations'),
(1237, 134, 'Guest book stations'),
(1238, 134, 'Interactive walls'),
(1239, 134, 'Hashtag printers'),
(1240, 135, 'Wedding cars'),
(1241, 135, 'Classic cars'),
(1242, 135, 'Vintage cars'),
(1243, 135, 'Luxury cars'),
(1244, 135, 'Horse and carriage'),
(1245, 135, 'Campervan hire'),
(1246, 135, 'Limousine hire'),
(1247, 136, 'Minibus hire'),
(1248, 136, 'Coach hire'),
(1249, 136, 'Shuttle buses'),
(1250, 136, 'Taxi coordination'),
(1251, 136, 'Accessible transport'),
(1252, 136, 'Airport transfers'),
(1253, 137, 'Prom cars'),
(1254, 137, 'Supercar hire'),
(1255, 137, 'Motorcycle escort'),
(1256, 137, 'Tractor rides'),
(1257, 137, 'Novelty vehicles'),
(1258, 138, 'Bridal makeup'),
(1259, 138, 'Party makeup'),
(1260, 138, 'Prom makeup'),
(1261, 138, 'Special effects makeup'),
(1262, 138, 'Makeup trials'),
(1263, 138, 'Group makeup bookings'),
(1264, 139, 'Bridal hair'),
(1265, 139, 'Hair styling'),
(1266, 139, 'Prom hair'),
(1267, 139, 'Hair trials'),
(1268, 139, 'Mobile hairdressers'),
(1269, 139, 'Children’s hair styling'),
(1270, 140, 'Mobile massage'),
(1271, 140, 'Nail technicians'),
(1272, 140, 'Spray tanning'),
(1273, 140, 'Pamper parties'),
(1274, 140, 'Men’s grooming'),
(1275, 140, 'Skincare treatments'),
(1276, 141, 'Wedding invitations'),
(1277, 141, 'Birthday invitations'),
(1278, 141, 'Corporate invitations'),
(1279, 141, 'Save the dates'),
(1280, 141, 'RSVP cards'),
(1281, 141, 'Digital invitations'),
(1282, 142, 'Order of service'),
(1283, 142, 'Menus'),
(1284, 142, 'Place cards'),
(1285, 142, 'Table numbers'),
(1286, 142, 'Table plans'),
(1287, 142, 'Welcome boards'),
(1288, 143, 'Banners'),
(1289, 143, 'Posters'),
(1290, 143, 'Flyers'),
(1291, 143, 'Programmes'),
(1292, 143, 'Branded signage'),
(1293, 143, 'Stickers and labels'),
(1294, 144, 'Wedding favours'),
(1295, 144, 'Christening favours'),
(1296, 144, 'Birthday favours'),
(1297, 144, 'Corporate favours'),
(1298, 144, 'Personalised sweets'),
(1299, 144, 'Mini gifts'),
(1300, 145, 'Engraved gifts'),
(1301, 145, 'Printed gifts'),
(1302, 145, 'Photo gifts'),
(1303, 145, 'Personalised clothing'),
(1304, 145, 'Keepsake boxes'),
(1305, 145, 'Custom illustrations'),
(1306, 146, 'Branded merchandise'),
(1307, 146, 'Staff gifts'),
(1308, 146, 'Client gifts'),
(1309, 146, 'Award gifts'),
(1310, 146, 'Welcome packs'),
(1311, 147, 'Wedding planners'),
(1312, 147, 'Party planners'),
(1313, 147, 'Corporate event planners'),
(1314, 147, 'On-the-day coordinators'),
(1315, 147, 'Venue finding'),
(1316, 147, 'Supplier sourcing'),
(1317, 148, 'Event managers'),
(1318, 148, 'Toastmasters'),
(1319, 148, 'Masters of ceremonies'),
(1320, 148, 'Stewards'),
(1321, 148, 'Ticketing support'),
(1322, 148, 'Registration desk staff'),
(1323, 149, 'Guest list management'),
(1324, 149, 'RSVP management'),
(1325, 149, 'Budget planning'),
(1326, 149, 'Risk assessments'),
(1327, 149, 'Event schedules'),
(1328, 150, 'Marquees'),
(1329, 150, 'Tipis'),
(1330, 150, 'Stretch tents'),
(1331, 150, 'Yurts'),
(1332, 150, 'Gazebos'),
(1333, 150, 'Clearspan structures'),
(1334, 150, 'Pagodas'),
(1335, 151, 'Outdoor flooring'),
(1336, 151, 'Temporary toilets'),
(1337, 151, 'Outdoor bars'),
(1338, 151, 'Outdoor kitchens'),
(1339, 151, 'Fencing'),
(1340, 151, 'Trackway'),
(1341, 151, 'Power distribution'),
(1342, 152, 'Patio heaters'),
(1343, 152, 'Blanket hire'),
(1344, 152, 'Umbrella hire'),
(1345, 152, 'Wet weather covers'),
(1346, 152, 'Cooling systems'),
(1347, 153, 'PA systems'),
(1348, 153, 'Microphone hire'),
(1349, 153, 'Sound engineers'),
(1350, 153, 'Background music systems'),
(1351, 153, 'Conference audio'),
(1352, 153, 'Wireless microphones'),
(1353, 154, 'Projector hire'),
(1354, 154, 'Screens'),
(1355, 154, 'LED screens'),
(1356, 154, 'TV hire'),
(1357, 154, 'Presentation equipment'),
(1358, 154, 'Video walls'),
(1359, 155, 'Stage hire'),
(1360, 155, 'Staging crews'),
(1361, 155, 'Lighting technicians'),
(1362, 155, 'Hybrid event support'),
(1363, 155, 'Live streaming production'),
(1364, 155, 'Technical event management'),
(1365, 156, 'Wedding venues'),
(1366, 156, 'Hotels'),
(1367, 156, 'Country houses'),
(1368, 156, 'Barn venues'),
(1369, 156, 'Village halls'),
(1370, 156, 'Community centres'),
(1371, 156, 'Restaurants'),
(1372, 157, 'Conference centres'),
(1373, 157, 'Meeting rooms'),
(1374, 157, 'Training venues'),
(1375, 157, 'Exhibition spaces'),
(1376, 157, 'Networking venues'),
(1377, 158, 'Outdoor venues'),
(1378, 158, 'Historic venues'),
(1379, 158, 'Museums'),
(1380, 158, 'Galleries'),
(1381, 158, 'Sports clubs'),
(1382, 158, 'Warehouses'),
(1383, 158, 'Gardens'),
(1384, 159, 'Art workshops'),
(1385, 159, 'Craft workshops'),
(1386, 159, 'Flower crown workshops'),
(1387, 159, 'Pottery painting'),
(1388, 159, 'Life drawing'),
(1389, 159, 'Cooking classes'),
(1390, 160, 'Inflatable games'),
(1391, 160, 'Garden games'),
(1392, 160, 'Archery'),
(1393, 160, 'Laser tag'),
(1394, 160, 'Axe throwing'),
(1395, 160, 'Sports day games'),
(1396, 160, 'Team building games'),
(1397, 161, 'Petting zoos'),
(1398, 161, 'Pony parties'),
(1399, 161, 'Bird of prey displays'),
(1400, 161, 'Reptile encounters'),
(1401, 161, 'Animal therapy visits'),
(1402, 162, 'Waiting staff'),
(1403, 162, 'Bar staff'),
(1404, 162, 'Hosts and hostesses'),
(1405, 162, 'Cloakroom staff'),
(1406, 162, 'Cleaning staff'),
(1407, 162, 'Kitchen porters'),
(1408, 163, 'Door supervisors'),
(1409, 163, 'Event security'),
(1410, 163, 'Crowd management'),
(1411, 163, 'Car park marshals'),
(1412, 163, 'Overnight security'),
(1413, 164, 'First aiders'),
(1414, 164, 'Medics'),
(1415, 164, 'Fire marshals'),
(1416, 164, 'Chaperones'),
(1417, 164, 'Accessibility support staff'),
(1418, 165, 'Hotel room blocks'),
(1419, 165, 'Guest houses'),
(1420, 165, 'Glamping accommodation'),
(1421, 165, 'Serviced apartments'),
(1422, 165, 'Group accommodation'),
(1423, 166, 'Travel coordination'),
(1424, 166, 'Guest transport planning'),
(1425, 166, 'Airport pickup coordination'),
(1426, 166, 'Itinerary planning'),
(1427, 166, 'Local area guides'),
(1428, 167, 'Celebrants'),
(1429, 167, 'Registrars'),
(1430, 167, 'Interpreters'),
(1431, 167, 'Pet sitters'),
(1432, 167, 'Childcare services'),
(1433, 167, 'Cleaning services'),
(1434, 168, 'Event websites'),
(1435, 168, 'Online invitations'),
(1436, 168, 'Ticketing platforms'),
(1437, 168, 'QR code check-in'),
(1438, 168, 'Digital seating plans'),
(1439, 169, 'Custom requests'),
(1440, 169, 'Unusual suppliers'),
(1441, 169, 'Multi-service packages'),
(1442, 169, 'Not listed elsewhere')
ON DUPLICATE KEY UPDATE `parent_id`=VALUES(`parent_id`), `name`=VALUES(`name`);

-- --------------------------------------------------------
-- Table: subcategories
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `subcategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `category_id` int(11) DEFAULT NULL,
  `subcategory_id` int(11) DEFAULT NULL,
  `third_category_id` int(11) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `free_coverage_radius` int(11) DEFAULT NULL,
  `paid_coverage_radius` int(11) DEFAULT NULL,
  `travel_fee_per_km` decimal(10,2) DEFAULT NULL,
  `cancellation_policy` text DEFAULT NULL,
  `service_tags` text DEFAULT NULL,
  `service_location` varchar(255) DEFAULT NULL,
  `all_travel_included` tinyint(1) DEFAULT 0,
  `no_travel_limit` tinyint(1) DEFAULT 0,
  `event_types` text DEFAULT NULL,
  `commission_percentage` decimal(5,2) DEFAULT NULL,
  `license` varchar(255) DEFAULT NULL,
  `attendance_thresholds` text DEFAULT NULL,
  `max_pitch_fees` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vendor_id` (`vendor_id`),
  CONSTRAINT `services_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `services` (`id`, `vendor_id`, `title`, `description`, `image`, `price`, `category_id`, `subcategory_id`, `third_category_id`, `status`, `cancellation_policy`) VALUES
(2, 3, 'Sweetie Sweet Cart', 'Indulge your sweet tooth at the Sweetie Cart Candy Stall, a whimsical haven for candy lovers of all ages!', '1716936655_2a9474e339e1b2141db3.jpg', 150.00, 1, 104, 1035, 'active', 'Cancel up to 14 days before for a full refund of your deposit.'),
(3, 3, 'Sweetie Sweet Cart', 'Indulge your sweet tooth at the Sweetie Cart Candy Stall, a whimsical haven for candy lovers of all ages!', '1716936809_ea5338b2e4ba5823d2f9.jpg', 150.00, 1, 104, 1035, 'active', 'Cancel up to 14 days before for a full refund of your deposit.'),
(4, 3, 'Mr Beatys Burgers', 'BurgerBurgerBurgerBurgerBurgerBurgerBurger', '1716937372_8e6a7964ed534149d3cb.jpeg', 240.00, 1, 101, 1009, 'active', '48 hours notice required for deposit refund.'),
(5, 3, 'Dinky Donuts', 'Delight in the irresistible aroma and melt-in-your-mouth goodness of Dinky Donuts!', '1716937744_df87fb10763e1b292fb8.jpeg', 90.00, 1, 104, 1038, 'active', 'Cancel up to 7 days before for a full refund.'),
(90, 3, '(Inactive QA) Vintage photobooth', 'Seeded inactive listing for vendor dashboard QA (services tab + filters).', NULL, 320.00, 11, 132, 1224, 'inactive', 'Full refund up to 30 days before the event.')
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`), `status`=VALUES(`status`), `cancellation_policy`=VALUES(`cancellation_policy`), `category_id`=VALUES(`category_id`), `subcategory_id`=VALUES(`subcategory_id`), `third_category_id`=VALUES(`third_category_id`);

-- --------------------------------------------------------
-- Table: service_images
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `service_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `thumbnail_path` varchar(500) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `service_images` (`id`, `service_id`, `image_path`, `thumbnail_path`, `is_primary`) VALUES
(2, 2, 'uploads/services/1716936655_2a9474e339e1b2141db3.jpg', 'uploads/services/thumb_1716936655_2a9474e339e1b2141db3.jpg', 1),
(3, 3, 'uploads/services/1716936809_ea5338b2e4ba5823d2f9.jpg', 'uploads/services/thumb_1716936809_ea5338b2e4ba5823d2f9.jpg', 1),
(4, 4, 'uploads/services/1716937372_8e6a7964ed534149d3cb.jpeg', 'uploads/services/thumb_1716937372_8e6a7964ed534149d3cb.jpeg', 1),
(5, 5, 'uploads/services/1716937744_df87fb10763e1b292fb8.jpeg', 'uploads/services/thumb_1716937744_df87fb10763e1b292fb8.jpeg', 1)
ON DUPLICATE KEY UPDATE `image_path`=VALUES(`image_path`), `thumbnail_path`=VALUES(`thumbnail_path`), `is_primary`=VALUES(`is_primary`);

-- --------------------------------------------------------
-- Table: events
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `venue_name` varchar(255) DEFAULT NULL,
  `postcode` varchar(20) DEFAULT NULL,
  `town_city` varchar(255) DEFAULT NULL,
  `indoor_outdoor` varchar(20) DEFAULT NULL,
  `budget_min` decimal(10,2) DEFAULT NULL,
  `budget_max` decimal(10,2) DEFAULT NULL,
  `style_theme` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `category` varchar(255) DEFAULT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `guest_count` int(11) DEFAULT NULL,
  `event_setting` varchar(20) NOT NULL DEFAULT 'private',
  `organiser_pitch_fee` decimal(10,2) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vendor_id` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: bookings
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `payment_intent_id` varchar(255) DEFAULT NULL,
  `balance_due` decimal(10,2) DEFAULT NULL,
  `payment_plan` varchar(32) DEFAULT 'single',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: booking_items
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `booking_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `package_name` varchar(255) DEFAULT NULL,
  `guest_count` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `quote_breakdown` json DEFAULT NULL,
  `quote_warnings` json DEFAULT NULL,
  `extras_snapshot` json DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: payments
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `payment_intent_id` varchar(255) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT 'pending',
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'gbp',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_type` varchar(50) DEFAULT 'deposit',
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: event_basket_items
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `event_basket_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `extras` text DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `deposit_amount` decimal(10,2) DEFAULT NULL,
  `estimated_total` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `quote_breakdown` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  KEY `user_id` (`user_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: carts (legacy)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `carts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: chat_rooms
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `chat_rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: chat_messages
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chat_room_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `original_message` text DEFAULT NULL,
  `moderation_status` varchar(20) NOT NULL DEFAULT 'clean',
  `admin_note` text DEFAULT NULL,
  `profanity_matches` varchar(500) DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: favourites
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `favourites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_service` (`user_id`, `service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: unavailable_dates
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `unavailable_dates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: service_availability
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `service_availability` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `day_of_week` varchar(20) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: service_time_blocks
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `service_time_blocks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: service_public_event_data
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `service_public_event_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `data` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_locations
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `service_location` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `all_travel_included` tinyint(1) DEFAULT 0,
  `no_travel_limit` tinyint(1) DEFAULT 0,
  `free_coverage_radius` int(11) DEFAULT NULL,
  `paid_coverage_radius` int(11) DEFAULT NULL,
  `travel_fee_per_km` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_tags
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_service_tags
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_service_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_event_types
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_event_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `event_type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_guest_based_pricing
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_guest_based_pricing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `private_event_pricing_id` int(11) DEFAULT NULL,
  `min_guest` int(11) DEFAULT NULL,
  `max_guest` int(11) DEFAULT NULL,
  `guest_price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_custom_duration_pricing
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_custom_duration_pricing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `private_event_pricing_id` int(11) DEFAULT NULL,
  `duration_type` varchar(20) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_tiered_packages_pricing
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_tiered_packages_pricing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `private_event_pricing_id` int(11) DEFAULT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `package_description` text DEFAULT NULL,
  `package_price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_quantity_pricing
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_quantity_pricing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `private_event_pricing_id` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_quantity` int(11) NOT NULL DEFAULT 1,
  `max_quantity` int(11) DEFAULT NULL,
  `unit_label` varchar(50) NOT NULL DEFAULT 'items',
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`),
  KEY `private_event_pricing_id` (`private_event_pricing_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_private_event_pricing
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_private_event_pricing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `pricing_type` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_cancellation_policies
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_cancellation_policies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `policy` text DEFAULT NULL,
  `cancellation_policy` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_public_event_pricing
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_public_event_pricing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `commission_percentage` decimal(5,2) DEFAULT NULL,
  `min_attendance` int(11) DEFAULT NULL,
  `max_attendance` int(11) DEFAULT NULL,
  `max_pitch_fee` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_corporate_event_pricing
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_corporate_event_pricing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `pricing_details` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: services_optional_extras
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services_optional_extras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- QA / demo data (events, bookings, messaging, favourites)
-- Idempotent via ON DUPLICATE KEY UPDATE on primary keys.
-- Test accounts (password TestPass123!): admin, qa_customer, m.pearson1 (vendor), mark90 (customer)
-- --------------------------------------------------------

INSERT INTO `events` (`id`, `user_id`, `title`, `description`, `date`, `location`, `event_type`, `guest_count`, `status`) VALUES
(501, 6, 'QA Sample Wedding', 'Seeded private event for dashboard and booking QA.', '2026-09-15', 'Manchester Town Hall', 'Wedding', 80, 'active')
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`), `description`=VALUES(`description`), `date`=VALUES(`date`), `location`=VALUES(`location`);

INSERT INTO `bookings` (`id`, `user_id`, `event_id`, `status`) VALUES
(501, 6, 501, 'pending')
ON DUPLICATE KEY UPDATE `user_id`=VALUES(`user_id`), `event_id`=VALUES(`event_id`), `status`=VALUES(`status`);

INSERT INTO `booking_items` (`id`, `booking_id`, `service_id`, `quantity`, `price`, `status`) VALUES
(501, 501, 2, 1, 150.00, 'pending'),
(502, 501, 5, 1, 90.00, 'accepted')
ON DUPLICATE KEY UPDATE `price`=VALUES(`price`), `status`=VALUES(`status`);

INSERT INTO `payments` (`id`, `booking_id`, `payment_status`, `amount_paid`, `description`, `payment_type`) VALUES
(501, 501, 'succeeded', 75.00, 'QA seed deposit', 'deposit')
ON DUPLICATE KEY UPDATE `payment_status`=VALUES(`payment_status`), `amount_paid`=VALUES(`amount_paid`);

INSERT INTO `favourites` (`user_id`, `service_id`) VALUES (6, 4)
ON DUPLICATE KEY UPDATE `user_id`=VALUES(`user_id`);

INSERT INTO `chat_rooms` (`id`, `vendor_id`, `customer_id`, `service_id`) VALUES
(501, 3, 6, 2)
ON DUPLICATE KEY UPDATE `vendor_id`=VALUES(`vendor_id`), `customer_id`=VALUES(`customer_id`), `service_id`=VALUES(`service_id`);

INSERT INTO `chat_messages` (`id`, `chat_room_id`, `sender_id`, `receiver_id`, `message`, `is_read`, `moderation_status`) VALUES
(501, 501, 6, 3, 'Hi — confirming our sweet cart for the wedding. Thanks!', 0, 'clean'),
(502, 501, 3, 6, 'Thanks, we will confirm closer to the date.', 1, 'clean')
ON DUPLICATE KEY UPDATE `message`=VALUES(`message`), `is_read`=VALUES(`is_read`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
