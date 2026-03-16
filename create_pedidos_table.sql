-- Ejecuta este SQL en phpMyAdmin o en tu cliente MySQL
-- Base de datos: night_market_db

CREATE TABLE IF NOT EXISTS `pedidos` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `comercio_id`   INT NOT NULL,
    `cliente_zona`  VARCHAR(150) NOT NULL DEFAULT '',
    `items_json`    LONGTEXT NOT NULL,
    `subtotal`      DECIMAL(10,2) NOT NULL DEFAULT 0,
    `descuento`     DECIMAL(10,2) NOT NULL DEFAULT 0,
    `envio`         DECIMAL(10,2) NOT NULL DEFAULT 0,
    `total`         DECIMAL(10,2) NOT NULL DEFAULT 0,
    `cupon`         VARCHAR(50) NULL DEFAULT NULL,
    `instrucciones` TEXT NULL DEFAULT NULL,
    `estado`        ENUM('pendiente','aceptado','en_preparacion','en_camino','entregado','cancelado')
                    NOT NULL DEFAULT 'pendiente',
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_comercio` (`comercio_id`),
    INDEX `idx_estado`   (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
