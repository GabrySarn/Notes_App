CREATE TABLE users(
  id INT AUTO_INCREMENT,
  name VARCHAR(60) NOT NULL,
  surname VARCHAR(60) NOT NULL,
  email VARCHAR(60) NOT NULL ,
  password VARCHAR(256) NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE notes(
    id INT AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY(id),
    FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE folders(
    id INT AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL ,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE categories(
    id INT AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE note_folder(
    note_id INT NOT NULL,
    folder_id INT NOT NULL,
    FOREIGN KEY (note_id) REFERENCES notes(id),
    FOREIGN KEY (folder_id) REFERENCES folders(id)
);

CREATE TABLE note_category(
    note_id INT NOT NULL,
    category_id INT NOT NULL,
    FOREIGN KEY (note_id) REFERENCES notes(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);