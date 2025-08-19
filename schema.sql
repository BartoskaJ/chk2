CREATE TABLE topics (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO topics (name) VALUES ('Safety'), ('Quality'), ('Maintenance');

CREATE TABLE form_templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  topic_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  schema_json JSON,
  is_active TINYINT DEFAULT 1,
  schedule_active TINYINT DEFAULT 0,
  schedule_freq ENUM('DAILY','WEEKLY','MONTHLY','YEARLY') DEFAULT 'DAILY',
  schedule_interval INT DEFAULT 1,
  schedule_time TIME DEFAULT '08:00:00',
  schedule_notify_emails TEXT,
  schedule_next_run DATETIME NULL,
  schedule_last_run DATETIME NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX(topic_id),
  INDEX(is_active),
  INDEX(schedule_active),
  INDEX(schedule_next_run)
);

CREATE TABLE form_entries (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  template_id INT NOT NULL,
  topic_id INT NOT NULL,
  data_json JSON,
  created_by VARCHAR(100),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX(template_id),
  INDEX(topic_id),
  INDEX(created_at),
  INDEX(created_by)
);
