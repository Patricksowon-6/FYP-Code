DROP TABLE IF EXISTS follows, likes, photos, users;


CREATE TABLE users 
(
user_id serial PRIMARY KEY,
username VARCHAR(30) UNIQUE NOT NULL,
created_at TIMESTAMP DEFAULT NOW()
);


CREATE TABLE photos 
(
photo_id serial PRIMARY KEY,
image_url VARCHAR(255) NOT NULL,
user_id INTEGER REFERENCES users(user_id),
published_at TIMESTAMP DEFAULT NOW()
);


CREATE TABLE likes 
(
user_id INTEGER NOT NULL REFERENCES users(user_id),
photo_id INTEGER NOT NULL REFERENCES photos(photo_id),
liked_at TIMESTAMP DEFAULT NOW(),
PRIMARY KEY(user_id, photo_id)
);


CREATE TABLE follows 
(
follower_id INTEGER NOT NULL REFERENCES users(user_id),
followee_id INTEGER NOT NULL REFERENCES users(user_id),
followed_at TIMESTAMP DEFAULT NOW(),
PRIMARY KEY(follower_id, followee_id)
);



-- Insert data into the users table
INSERT INTO users (username, created_at) VALUES
('john_doe', '2023-09-13 10:00:00'),
('jane_smith', '2023-09-13 10:05:00'),
('mike_jackson', '2023-09-13 10:10:00'),
('sarah_johnson', '2023-09-13 10:15:00'),
('chris_wilson', '2023-09-13 10:20:00'),
('emily_brown', '2023-09-13 10:25:00'),
('david_clark', '2023-09-13 10:30:00'),
('laura_adams', '2023-09-13 10:35:00'),
('william_martin', '2023-09-13 10:40:00'),
('olivia_turner', '2023-09-13 10:45:00');
-- Insert data into the photos table
INSERT INTO photos (image_url, user_id, published_at) VALUES
('https://example.com/photo1.jpg', 1, '2023-09-13 11:00:00'),
('https://example.com/photo2.jpg', 2, '2023-09-13 11:05:00'),
('https://example.com/photo3.jpg', 3, '2023-09-13 11:10:00'),
('https://example.com/photo4.jpg', 4, '2023-09-13 11:15:00'),
('https://example.com/photo5.jpg', 5, '2023-09-13 11:20:00'),
('https://example.com/photo6.jpg', 6, '2023-09-13 11:25:00'),
('https://example.com/photo7.jpg', 7, '2023-09-13 11:30:00'),
('https://example.com/photo8.jpg', 8, '2023-09-13 11:35:00'),
('https://example.com/photo9.jpg', 9, '2023-09-13 11:40:00'),
('https://example.com/photo10.jpg', 10, '2023-09-13 11:45:00');


-- Insert data into the likes table
INSERT INTO likes (user_id, photo_id, liked_at) VALUES
(1, 1, '2023-09-13 12:00:00'),
(2, 1, '2023-09-13 12:05:00'),
(3, 2, '2023-09-13 12:10:00'),
(4, 2, '2023-09-13 12:15:00'),
(5, 3, '2023-09-13 12:20:00'),
(6, 3, '2023-09-13 12:25:00'),
(7, 4, '2023-09-13 12:30:00'),
(8, 4, '2023-09-13 12:35:00'),
(9, 5, '2023-09-13 12:40:00'),
(10, 5, '2023-09-13 12:45:00');

-- Insert data into the follows table
INSERT INTO follows (follower_id, followee_id, followed_at) VALUES
(1, 2, '2023-09-13 13:00:00'),
(1, 3, '2023-09-13 13:05:00'),
(2, 4, '2023-09-13 13:10:00'),
(2, 5, '2023-09-13 13:15:00'),
(3, 6, '2023-09-13 13:20:00'),
(3, 7, '2023-09-13 13:25:00'),
(4, 8, '2023-09-13 13:30:00'),
(4, 9, '2023-09-13 13:35:00'),
(5, 10, '2023-09-13 13:40:00'),
(6, 10, '2023-09-13 13:45:00');



select * from follows;
select * from likes;
select * from photos;
select * from users;


