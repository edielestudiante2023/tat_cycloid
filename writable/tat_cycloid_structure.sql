-- Estructura replicada de producción
-- Generado: 2026-04-03 14:41:52

-- Tabla: accesos
DROP TABLE IF EXISTS `accesos`;
CREATE TABLE "accesos" (
  "id_acceso" int NOT NULL AUTO_INCREMENT,
  "nombre" varchar(255) NOT NULL,
  "url" varchar(255) NOT NULL,
  "dimension" varchar(50) DEFAULT NULL,
  PRIMARY KEY ("id_acceso")
);

-- Tabla: capacitaciones_sst
DROP TABLE IF EXISTS `capacitaciones_sst`;
CREATE TABLE "capacitaciones_sst" (
  "id_capacitacion" int NOT NULL AUTO_INCREMENT,
  "capacitacion" varchar(255) NOT NULL,
  "objetivo_capacitacion" text NOT NULL,
  "observaciones" text,
  PRIMARY KEY ("id_capacitacion")
);

-- Tabla: client_policies
DROP TABLE IF EXISTS `client_policies`;
CREATE TABLE "client_policies" (
  "id" int NOT NULL AUTO_INCREMENT,
  "client_id" int NOT NULL,
  "policy_type_id" int NOT NULL,
  "policy_content" text,
  "created_at" datetime DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id")
);

-- Tabla: dashboard_items
DROP TABLE IF EXISTS `dashboard_items`;
CREATE TABLE "dashboard_items" (
  "id" int NOT NULL AUTO_INCREMENT,
  "rol" varchar(50) NOT NULL,
  "tipo_proceso" varchar(50) NOT NULL,
  "detalle" varchar(255) NOT NULL,
  "descripcion" text,
  "accion_url" varchar(255) DEFAULT NULL,
  "orden" int DEFAULT '0',
  "categoria" varchar(100) DEFAULT NULL,
  "icono" varchar(100) DEFAULT NULL,
  "color_gradiente" varchar(100) DEFAULT NULL,
  "target_blank" tinyint(1) DEFAULT '1',
  "activo" tinyint(1) DEFAULT '1',
  "creado_en" datetime DEFAULT CURRENT_TIMESTAMP,
  "actualizado_en" datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id")
);

-- Tabla: detail_report
DROP TABLE IF EXISTS `detail_report`;
CREATE TABLE "detail_report" (
  "id_detailreport" int NOT NULL AUTO_INCREMENT,
  "detail_report" varchar(255) NOT NULL,
  PRIMARY KEY ("id_detailreport"),
  KEY "idx_detail_report" ("detail_report")
);

-- Tabla: document_versions
DROP TABLE IF EXISTS `document_versions`;
CREATE TABLE "document_versions" (
  "id" int NOT NULL AUTO_INCREMENT,
  "client_id" int DEFAULT NULL,
  "policy_type_id" int DEFAULT NULL,
  "version_number" int DEFAULT NULL,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "document_type" varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "acronym" varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "location" varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "status" varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "change_control" text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY ("id")
);

-- Tabla: estandares
DROP TABLE IF EXISTS `estandares`;
CREATE TABLE "estandares" (
  "id_estandar" int NOT NULL AUTO_INCREMENT,
  "nombre" varchar(255) NOT NULL,
  PRIMARY KEY ("id_estandar")
);

-- Tabla: estandares_accesos
DROP TABLE IF EXISTS `estandares_accesos`;
CREATE TABLE "estandares_accesos" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_estandar" int NOT NULL,
  "id_acceso" int NOT NULL,
  PRIMARY KEY ("id")
);

-- Tabla: evaluacion_inicial_sst
DROP TABLE IF EXISTS `evaluacion_inicial_sst`;
CREATE TABLE "evaluacion_inicial_sst" (
  "id_ev_ini" int NOT NULL AUTO_INCREMENT,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "id_cliente" int DEFAULT NULL,
  "ciclo" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estandar" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "detalle_estandar" text COLLATE utf8mb4_unicode_ci,
  "estandares_minimos" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "numeral" varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "numerales_del_cliente" int DEFAULT NULL,
  "siete" int DEFAULT NULL,
  "veintiun" int DEFAULT NULL,
  "sesenta" int DEFAULT NULL,
  "item_del_estandar" text COLLATE utf8mb4_unicode_ci,
  "evaluacion_inicial" varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "valor" decimal(5,2) DEFAULT NULL,
  "puntaje_cuantitativo" decimal(5,2) DEFAULT NULL,
  "item" text COLLATE utf8mb4_unicode_ci,
  "criterio" text COLLATE utf8mb4_unicode_ci,
  "modo_de_verificacion" text COLLATE utf8mb4_unicode_ci,
  "calificacion" decimal(5,2) DEFAULT NULL,
  "nivel_de_evaluacion" varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY ("id_ev_ini")
);

-- Tabla: historial_resumen_estandares
DROP TABLE IF EXISTS `historial_resumen_estandares`;
CREATE TABLE "historial_resumen_estandares" (
  "id_cliente" int NOT NULL,
  "nombre_cliente" varchar(255) DEFAULT NULL,
  "estandares" text,
  "nombre_consultor" varchar(255) DEFAULT NULL,
  "correo_consultor" varchar(255) DEFAULT NULL,
  "total_valor" decimal(10,2) DEFAULT NULL,
  "total_puntaje" decimal(10,2) DEFAULT NULL,
  "porcentaje_cumplimiento" decimal(5,2) DEFAULT NULL,
  "fecha_extraccion" datetime NOT NULL,
  PRIMARY KEY ("id_cliente","fecha_extraccion")
);

-- Tabla: historial_resumen_plan_trabajo
DROP TABLE IF EXISTS `historial_resumen_plan_trabajo`;
CREATE TABLE "historial_resumen_plan_trabajo" (
  "id_cliente" int NOT NULL,
  "nombre_cliente" varchar(255) DEFAULT NULL,
  "estandares" text,
  "nombre_consultor" varchar(255) DEFAULT NULL,
  "correo_consultor" varchar(255) DEFAULT NULL,
  "total_actividades" int DEFAULT NULL,
  "actividades_abiertas" int DEFAULT NULL,
  "porcentaje_abiertas" decimal(5,2) DEFAULT NULL,
  "fecha_extraccion" datetime NOT NULL,
  PRIMARY KEY ("id_cliente","fecha_extraccion")
);

-- Tabla: policy_types
DROP TABLE IF EXISTS `policy_types`;
CREATE TABLE "policy_types" (
  "id" int NOT NULL AUTO_INCREMENT,
  "type_name" varchar(255) NOT NULL,
  "description" text,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id")
);

-- Tabla: prueba
DROP TABLE IF EXISTS `prueba`;
CREATE TABLE "prueba" (
  "idprueba" int NOT NULL,
  "nombre_prueba" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
);

-- Tabla: report_type_table
DROP TABLE IF EXISTS `report_type_table`;
CREATE TABLE "report_type_table" (
  "id_report_type" int NOT NULL AUTO_INCREMENT,
  "report_type" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY ("id_report_type")
);

-- Tabla: tbl_acta_visita
DROP TABLE IF EXISTS `tbl_acta_visita`;
CREATE TABLE "tbl_acta_visita" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int NOT NULL,
  "fecha_visita" date NOT NULL,
  "hora_visita" time NOT NULL,
  "ubicacion_gps" varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Coordenadas GPS lat,lng',
  "motivo" varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  "modalidad" varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Presencial' COMMENT 'Presencial/Virtual/Mixta',
  "cartera" text COLLATE utf8mb4_unicode_ci,
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "pta_confirmado" tinyint NOT NULL DEFAULT '0',
  "proxima_reunion_fecha" date DEFAULT NULL,
  "proxima_reunion_hora" time DEFAULT NULL,
  "firma_administrador" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "firma_vigia" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "firma_consultor" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "motivo_sin_firma" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "token_firma_remota" varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "token_firma_tipo" varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "token_firma_expiracion" datetime DEFAULT NULL,
  "soporte_lavado_tanques" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "soporte_plagas" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','pendiente_firma','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "agenda_id" varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Vínculo opcional con agenda',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_acta_cliente" ("id_cliente"),
  KEY "idx_acta_consultor" ("id_consultor"),
  KEY "idx_acta_fecha" ("fecha_visita"),
  KEY "idx_acta_estado" ("estado"),
  CONSTRAINT "fk_acta_visita_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT "fk_acta_visita_consultor" FOREIGN KEY ("id_consultor") REFERENCES "tbl_consultor" ("id_consultor") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_acta_visita_fotos
DROP TABLE IF EXISTS `tbl_acta_visita_fotos`;
CREATE TABLE "tbl_acta_visita_fotos" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_acta_visita" int NOT NULL,
  "ruta_archivo" varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  "tipo" varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'foto' COMMENT 'foto, soporte, seg_social',
  "descripcion" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_foto_acta" ("id_acta_visita"),
  CONSTRAINT "fk_foto_acta" FOREIGN KEY ("id_acta_visita") REFERENCES "tbl_acta_visita" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabla: tbl_acta_visita_integrantes
DROP TABLE IF EXISTS `tbl_acta_visita_integrantes`;
CREATE TABLE "tbl_acta_visita_integrantes" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_acta_visita" int NOT NULL,
  "nombre" varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  "rol" varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ADMINISTRADOR, CONSULTOR CYCLOID, VIGÍA SST, etc.',
  "orden" tinyint NOT NULL DEFAULT '1' COMMENT 'Orden de aparición en el acta',
  PRIMARY KEY ("id"),
  KEY "idx_integrante_acta" ("id_acta_visita"),
  CONSTRAINT "fk_integrante_acta" FOREIGN KEY ("id_acta_visita") REFERENCES "tbl_acta_visita" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabla: tbl_acta_visita_pta
DROP TABLE IF EXISTS `tbl_acta_visita_pta`;
CREATE TABLE "tbl_acta_visita_pta" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_acta_visita" int NOT NULL,
  "id_ptacliente" int NOT NULL,
  "cerrada" tinyint(1) NOT NULL DEFAULT '0',
  "justificacion_no_cierre" text,
  "created_at" datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  UNIQUE KEY "uk_acta_pta" ("id_acta_visita","id_ptacliente"),
  KEY "id_ptacliente" ("id_ptacliente"),
  CONSTRAINT "tbl_acta_visita_pta_ibfk_1" FOREIGN KEY ("id_acta_visita") REFERENCES "tbl_acta_visita" ("id") ON DELETE CASCADE,
  CONSTRAINT "tbl_acta_visita_pta_ibfk_2" FOREIGN KEY ("id_ptacliente") REFERENCES "tbl_pta_cliente" ("id_ptacliente") ON DELETE CASCADE
);

-- Tabla: tbl_acta_visita_temas
DROP TABLE IF EXISTS `tbl_acta_visita_temas`;
CREATE TABLE "tbl_acta_visita_temas" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_acta_visita" int NOT NULL,
  "descripcion" text COLLATE utf8mb4_unicode_ci NOT NULL,
  "orden" tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY ("id"),
  KEY "idx_tema_acta" ("id_acta_visita"),
  CONSTRAINT "fk_tema_acta" FOREIGN KEY ("id_acta_visita") REFERENCES "tbl_acta_visita" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabla: tbl_agendamientos
DROP TABLE IF EXISTS `tbl_agendamientos`;
CREATE TABLE "tbl_agendamientos" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int NOT NULL,
  "fecha_visita" date NOT NULL,
  "hora_visita" time NOT NULL,
  "frecuencia" enum('mensual','bimensual','trimestral') NOT NULL DEFAULT 'mensual',
  "estado" enum('pendiente','confirmado','completado','cancelado') NOT NULL DEFAULT 'pendiente',
  "confirmacion_calendar" varchar(255) DEFAULT NULL COMMENT 'ID evento calendar o texto de confirmacion',
  "preparacion_cliente" text COMMENT 'Notas de preparacion del cliente',
  "observaciones" text,
  "email_enviado" tinyint(1) NOT NULL DEFAULT '0',
  "fecha_email_enviado" datetime DEFAULT NULL,
  "created_at" datetime DEFAULT NULL,
  "updated_at" datetime DEFAULT NULL,
  PRIMARY KEY ("id"),
  KEY "idx_cliente" ("id_cliente"),
  KEY "idx_consultor" ("id_consultor"),
  KEY "idx_fecha" ("fecha_visita"),
  KEY "idx_estado" ("estado")
);

-- Tabla: tbl_asistencia_induccion
DROP TABLE IF EXISTS `tbl_asistencia_induccion`;
CREATE TABLE "tbl_asistencia_induccion" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_sesion" date NOT NULL,
  "tema" text COLLATE utf8mb4_unicode_ci,
  "lugar" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "objetivo" text COLLATE utf8mb4_unicode_ci,
  "capacitador" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "tipo_charla" enum('induccion_reinduccion','reunion','charla','capacitacion','otros_temas') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "material" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "tiempo_horas" decimal(4,1) DEFAULT NULL,
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "ruta_pdf_asistencia" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "ruta_pdf_responsabilidades" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "evaluacion_habilitada" tinyint(1) NOT NULL DEFAULT '0',
  "evaluacion_token" varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY ("id"),
  KEY "idx_asist_ind_cliente" ("id_cliente"),
  KEY "idx_asist_ind_consultor" ("id_consultor"),
  KEY "idx_asist_ind_estado" ("estado"),
  KEY "idx_asist_ind_tipo" ("tipo_charla"),
  CONSTRAINT "fk_asist_ind_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_asistencia_induccion_asistente
DROP TABLE IF EXISTS `tbl_asistencia_induccion_asistente`;
CREATE TABLE "tbl_asistencia_induccion_asistente" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_asistencia" int NOT NULL,
  "nombre" varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  "cedula" varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  "cargo" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "firma" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_asist_det_master" ("id_asistencia"),
  CONSTRAINT "fk_asist_det_master" FOREIGN KEY ("id_asistencia") REFERENCES "tbl_asistencia_induccion" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabla: tbl_auditoria_zona_residuos
DROP TABLE IF EXISTS `tbl_auditoria_zona_residuos`;
CREATE TABLE "tbl_auditoria_zona_residuos" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_inspeccion" date NOT NULL,
  "estado_acceso" enum('bueno','regular','malo','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_acceso" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_techo_pared_pisos" enum('bueno','regular','malo','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_techo_pared_pisos" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_ventilacion" enum('bueno','regular','malo','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_ventilacion" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_prevencion_incendios" enum('bueno','regular','malo','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_prevencion_incendios" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_drenajes" enum('bueno','regular','malo','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_drenajes" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "proliferacion_plagas" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_proliferacion_plagas" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_recipientes" enum('bueno','regular','malo','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_recipientes" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_reciclaje" enum('bueno','regular','malo','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_reciclaje" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_iluminarias" enum('bueno','regular','malo','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_iluminarias" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_senalizacion" enum('bueno','regular','malo','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_senalizacion" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_limpieza_desinfeccion" enum('bueno','regular','malo','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_limpieza_desinfeccion" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_poseta" enum('bueno','regular','malo','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_poseta" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_aud_res_cliente" ("id_cliente"),
  KEY "idx_aud_res_consultor" ("id_consultor"),
  KEY "idx_aud_res_estado" ("estado"),
  CONSTRAINT "fk_aud_res_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_carta_vigia
DROP TABLE IF EXISTS `tbl_carta_vigia`;
CREATE TABLE "tbl_carta_vigia" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "nombre_vigia" varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  "documento_vigia" varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  "email_vigia" varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  "telefono_vigia" varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "token_firma" varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "token_firma_expiracion" datetime DEFAULT NULL,
  "estado_firma" enum('sin_enviar','pendiente_firma','firmado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sin_enviar',
  "firma_imagen" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "firma_ip" varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "firma_fecha" datetime DEFAULT NULL,
  "codigo_verificacion" varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_carta_vigia_cliente" ("id_cliente"),
  KEY "idx_carta_vigia_estado" ("estado_firma"),
  KEY "idx_carta_vigia_token" ("token_firma"),
  KEY "idx_carta_vigia_codigo" ("codigo_verificacion"),
  KEY "fk_carta_vigia_consultor" ("id_consultor"),
  CONSTRAINT "fk_carta_vigia_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_certificado_servicio
DROP TABLE IF EXISTS `tbl_certificado_servicio`;
CREATE TABLE "tbl_certificado_servicio" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_mantenimiento" int NOT NULL COMMENT '2=Lavado Tanques, 3=Fumigacion, 4=Desratizacion',
  "fecha_servicio" date NOT NULL,
  "archivo" varchar(500) DEFAULT NULL,
  "observaciones" text,
  "id_consultor" int DEFAULT NULL,
  "id_vencimiento" int DEFAULT NULL COMMENT 'FK tbl_vencimientos_mantenimientos, nullable',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_cliente_mant" ("id_cliente","id_mantenimiento"),
  KEY "idx_fecha" ("fecha_servicio")
);

-- Tabla: tbl_chat_log
DROP TABLE IF EXISTS `tbl_chat_log`;
CREATE TABLE "tbl_chat_log" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_usuario" int NOT NULL,
  "rol" varchar(20) NOT NULL,
  "tipo_operacion" varchar(50) NOT NULL,
  "detalle" text,
  "ip_address" varchar(45) DEFAULT NULL,
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_chatlog_usuario" ("id_usuario"),
  KEY "idx_chatlog_tipo" ("tipo_operacion"),
  KEY "idx_chatlog_fecha" ("created_at")
);

-- Tabla: tbl_ciclos_visita
DROP TABLE IF EXISTS `tbl_ciclos_visita`;
CREATE TABLE "tbl_ciclos_visita" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int NOT NULL,
  "anio" int NOT NULL,
  "mes_esperado" int NOT NULL,
  "estandar" varchar(50) DEFAULT NULL,
  "fecha_agendada" date DEFAULT NULL,
  "id_agendamiento" int DEFAULT NULL,
  "fecha_acta" date DEFAULT NULL,
  "id_acta" int DEFAULT NULL,
  "estatus_agenda" enum('pendiente','cumple','incumple') DEFAULT 'pendiente',
  "estatus_mes" enum('pendiente','cumple','incumple') DEFAULT 'pendiente',
  "alerta_enviada" tinyint(1) DEFAULT '0',
  "confirmacion_enviada" tinyint(1) DEFAULT '0',
  "observaciones" text,
  "created_at" datetime DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_cliente" ("id_cliente"),
  KEY "idx_consultor" ("id_consultor"),
  KEY "idx_mes_anio" ("mes_esperado","anio"),
  KEY "idx_estatus_agenda" ("estatus_agenda"),
  KEY "idx_estatus_mes" ("estatus_mes")
);

-- Tabla: tbl_client_kpi
DROP TABLE IF EXISTS `tbl_client_kpi`;
CREATE TABLE "tbl_client_kpi" (
  "id_client_kpi" int NOT NULL AUTO_INCREMENT,
  "created_at" timestamp NULL DEFAULT NULL,
  "updated_at" timestamp NULL DEFAULT NULL,
  "year" year DEFAULT NULL,
  "month" int DEFAULT NULL,
  "kpi_interpretation" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "id_cliente" int DEFAULT NULL,
  "id_kpi_policy" int DEFAULT NULL,
  "id_objectives" int DEFAULT NULL,
  "id_kpis" int DEFAULT NULL,
  "id_kpi_type" int DEFAULT NULL,
  "id_kpi_definition" int DEFAULT NULL,
  "kpi_target" int DEFAULT NULL,
  "kpi_formula" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "data_source" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "id_data_owner" int DEFAULT NULL,
  "positions_should_know_result" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "id_numerator_variable" int DEFAULT NULL,
  "id_denominator_variable" int DEFAULT NULL,
  "id_measurement_period" int DEFAULT NULL,
  "variable_numerador_1" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_numerador_1" int DEFAULT NULL,
  "variable_denominador_1" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_denominador_1" int DEFAULT NULL,
  "valor_indicador_1" decimal(10,2) DEFAULT NULL,
  "variable_numerador_2" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_numerador_2" int DEFAULT NULL,
  "variable_denominador_2" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_denominador_2" int DEFAULT NULL,
  "valor_indicador_2" decimal(10,2) DEFAULT NULL,
  "variable_numerador_3" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_numerador_3" int DEFAULT NULL,
  "variable_denominador_3" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_denominador_3" int DEFAULT NULL,
  "valor_indicador_3" decimal(10,2) DEFAULT NULL,
  "variable_numerador_4" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_numerador_4" int DEFAULT NULL,
  "variable_denominador_4" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_denominador_4" int DEFAULT NULL,
  "valor_indicador_4" decimal(10,2) DEFAULT NULL,
  "variable_numerador_5" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_numerador_5" int DEFAULT NULL,
  "variable_denominador_5" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_denominador_5" int DEFAULT NULL,
  "valor_indicador_5" decimal(10,2) DEFAULT NULL,
  "variable_numerador_6" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_numerador_6" int DEFAULT NULL,
  "variable_denominador_6" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_denominador_6" int DEFAULT NULL,
  "valor_indicador_6" decimal(10,2) DEFAULT NULL,
  "variable_numerador_7" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_numerador_7" int DEFAULT NULL,
  "variable_denominador_7" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_denominador_7" int DEFAULT NULL,
  "valor_indicador_7" decimal(10,2) DEFAULT NULL,
  "variable_numerador_8" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_numerador_8" int DEFAULT NULL,
  "variable_denominador_8" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_denominador_8" int DEFAULT NULL,
  "valor_indicador_8" decimal(10,2) DEFAULT NULL,
  "variable_numerador_9" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_numerador_9" int DEFAULT NULL,
  "variable_denominador_9" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_denominador_9" int DEFAULT NULL,
  "valor_indicador_9" decimal(10,2) DEFAULT NULL,
  "variable_numerador_10" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_numerador_10" int DEFAULT NULL,
  "variable_denominador_10" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_denominador_10" int DEFAULT NULL,
  "valor_indicador_10" decimal(10,2) DEFAULT NULL,
  "variable_numerador_11" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_numerador_11" int DEFAULT NULL,
  "variable_denominador_11" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_denominador_11" int DEFAULT NULL,
  "valor_indicador_11" decimal(10,2) DEFAULT NULL,
  "variable_numerador_12" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_numerador_12" int DEFAULT NULL,
  "variable_denominador_12" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dato_variable_denominador_12" int DEFAULT NULL,
  "valor_indicador_12" decimal(10,2) DEFAULT NULL,
  "gran_total_indicador" decimal(10,2) DEFAULT NULL,
  "analisis_datos" text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  "seguimiento1" text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  "seguimiento2" text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  "seguimiento3" text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  "periodicidad" varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mensual',
  PRIMARY KEY ("id_client_kpi"),
  UNIQUE KEY "id_client_kpi" ("id_client_kpi"),
  KEY "id_kpi_policy" ("id_kpi_policy"),
  KEY "id_objectives" ("id_objectives"),
  KEY "id_kpis" ("id_kpis"),
  KEY "id_kpi_type" ("id_kpi_type"),
  KEY "id_kpi_definition" ("id_kpi_definition"),
  KEY "id_data_owner" ("id_data_owner"),
  KEY "id_numerator_variable" ("id_numerator_variable"),
  KEY "id_denominator_variable" ("id_denominator_variable"),
  KEY "id_measurement_period" ("id_measurement_period"),
  KEY "id_cliente" ("id_cliente"),
  CONSTRAINT "tbl_client_kpi_ibfk_1" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente"),
  CONSTRAINT "tbl_client_kpi_ibfk_10" FOREIGN KEY ("id_measurement_period") REFERENCES "tbl_measurement_period" ("id_measurement_period"),
  CONSTRAINT "tbl_client_kpi_ibfk_11" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente"),
  CONSTRAINT "tbl_client_kpi_ibfk_2" FOREIGN KEY ("id_kpi_policy") REFERENCES "tbl_kpi_policy" ("id_kpi_policy"),
  CONSTRAINT "tbl_client_kpi_ibfk_3" FOREIGN KEY ("id_objectives") REFERENCES "tbl_objectives_policy" ("id_objectives"),
  CONSTRAINT "tbl_client_kpi_ibfk_4" FOREIGN KEY ("id_kpis") REFERENCES "tbl_kpis" ("id_kpis"),
  CONSTRAINT "tbl_client_kpi_ibfk_5" FOREIGN KEY ("id_kpi_type") REFERENCES "tbl_kpi_type" ("id_kpi_type"),
  CONSTRAINT "tbl_client_kpi_ibfk_6" FOREIGN KEY ("id_kpi_definition") REFERENCES "tbl_kpi_definition" ("id_kpi_definition"),
  CONSTRAINT "tbl_client_kpi_ibfk_7" FOREIGN KEY ("id_data_owner") REFERENCES "tbl_data_owner" ("id_data_owner"),
  CONSTRAINT "tbl_client_kpi_ibfk_8" FOREIGN KEY ("id_numerator_variable") REFERENCES "tbl_variable_numerator" ("id_numerator_variable"),
  CONSTRAINT "tbl_client_kpi_ibfk_9" FOREIGN KEY ("id_denominator_variable") REFERENCES "tbl_variable_denominator" ("id_denominator_variable")
);

-- Tabla: tbl_clientes
DROP TABLE IF EXISTS `tbl_clientes`;
CREATE TABLE "tbl_clientes" (
  "id_cliente" int NOT NULL AUTO_INCREMENT,
  "datetime" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "fecha_ingreso" date NOT NULL,
  "nit_cliente" int NOT NULL,
  "nombre_cliente" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "usuario" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "password" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "correo_cliente" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "correo_consejo_admon" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "telefono_1_cliente" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "telefono_2_cliente" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "direccion_cliente" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "persona_contacto_compras" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "persona_contacto_operaciones" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "persona_contacto_pagos" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "horarios_y_dias" text COLLATE utf8mb4_unicode_ci,
  "codigo_actividad_economica" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "nombre_rep_legal" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "cedula_rep_legal" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "fecha_fin_contrato" date DEFAULT NULL,
  "ciudad_cliente" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "estado" enum('activo','inactivo','pendiente','prospecto') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  "id_consultor" int NOT NULL,
  "vendedor" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "plazo_cartera" varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "fecha_cierre_facturacion" int DEFAULT NULL,
  "fecha_asignacion_cronograma" date DEFAULT NULL,
  "logo" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "rut" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "camara_comercio" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "cedula_rep_legal_doc" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "oferta_comercial" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "firma_representante_legal" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estandares" text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  "consultor_externo" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "email_consultor_externo" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "token_firma_alturas" varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "token_firma_alturas_exp" datetime DEFAULT NULL,
  "firma_alturas_fecha" datetime DEFAULT NULL,
  "firma_alturas_ip" varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "protocolo_alturas_firmado" tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY ("id_cliente"),
  UNIQUE KEY "id_cliente" ("id_cliente")
);

-- Tabla: tbl_consultor
DROP TABLE IF EXISTS `tbl_consultor`;
CREATE TABLE "tbl_consultor" (
  "id_consultor" int NOT NULL AUTO_INCREMENT,
  "nombre_consultor" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "cedula_consultor" int NOT NULL,
  "numero_licencia" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "foto_consultor" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "firma_consultor" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "usuario" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "password" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "correo_consultor" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "telefono_consultor" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "id_cliente" int NOT NULL,
  "rol" enum('consultant','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'consultant',
  PRIMARY KEY ("id_consultor"),
  UNIQUE KEY "id_consultor" ("id_consultor")
);

-- Tabla: tbl_contratos
DROP TABLE IF EXISTS `tbl_contratos`;
CREATE TABLE "tbl_contratos" (
  "id_contrato" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "numero_contrato" varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Número de contrato interno o referencia',
  "fecha_inicio" date NOT NULL COMMENT 'Fecha de inicio del contrato',
  "fecha_fin" date NOT NULL COMMENT 'Fecha de finalización del contrato',
  "valor_contrato" decimal(15,2) DEFAULT NULL COMMENT 'Valor monetario del contrato',
  "tipo_contrato" enum('inicial','renovacion','ampliacion') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inicial',
  "estado" enum('activo','vencido','cancelado','renovado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  "observaciones" text COLLATE utf8mb4_unicode_ci COMMENT 'Notas adicionales sobre el contrato',
  "clausula_cuarta_duracion" longtext COLLATE utf8mb4_unicode_ci COMMENT 'Texto personalizable de la cláusula cuarta sobre duración y plazo de ejecución del contrato',
  "nombre_rep_legal_cliente" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "cedula_rep_legal_cliente" varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "direccion_cliente" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "telefono_cliente" varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "email_cliente" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "nombre_rep_legal_contratista" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'DIANA PATRICIA CUESTAS NAVIA',
  "cedula_rep_legal_contratista" varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '52.425.982',
  "email_contratista" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'Diana.cuestas@cycloidtalent.com',
  "nombre_responsable_sgsst" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'Edison Ernesto Cuervo Salazar',
  "cedula_responsable_sgsst" varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '80.039.147',
  "licencia_responsable_sgsst" varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '4241',
  "email_responsable_sgsst" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'Edison.cuervo@cycloidtalent.com',
  "id_consultor_responsable" int DEFAULT NULL,
  "valor_mensual" decimal(15,2) DEFAULT NULL,
  "numero_cuotas" int DEFAULT '12',
  "frecuencia_visitas" varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'BIMENSUAL',
  "cuenta_bancaria" varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '108900260762',
  "banco" varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Davivienda',
  "tipo_cuenta" varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Ahorros',
  "contrato_generado" tinyint(1) DEFAULT '0',
  "fecha_generacion_contrato" datetime DEFAULT NULL,
  "ruta_pdf_contrato" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "contrato_enviado" tinyint(1) DEFAULT '0',
  "fecha_envio_contrato" datetime DEFAULT NULL,
  "email_envio_contrato" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "created_at" datetime DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "created_by" int DEFAULT NULL COMMENT 'ID del usuario que creó el registro',
  "token_firma" varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "token_firma_expiracion" datetime DEFAULT NULL,
  "estado_firma" enum('sin_enviar','pendiente_firma','firmado') COLLATE utf8mb4_unicode_ci DEFAULT 'sin_enviar',
  "firma_cliente_nombre" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "firma_cliente_cedula" varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "firma_cliente_imagen" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "firma_cliente_ip" varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "firma_cliente_fecha" datetime DEFAULT NULL,
  "clausula_primera_objeto" text COLLATE utf8mb4_unicode_ci,
  "codigo_verificacion" varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY ("id_contrato"),
  KEY "idx_cliente" ("id_cliente"),
  KEY "idx_fechas" ("fecha_inicio","fecha_fin"),
  KEY "idx_estado" ("estado"),
  KEY "idx_tipo" ("tipo_contrato"),
  KEY "idx_contrato_generado" ("contrato_generado"),
  KEY "idx_contrato_enviado" ("contrato_enviado"),
  KEY "idx_fecha_generacion" ("fecha_generacion_contrato"),
  KEY "fk_contracts_consultor" ("id_consultor_responsable"),
  CONSTRAINT "fk_contratos_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabla: tbl_cronog_capacitacion
DROP TABLE IF EXISTS `tbl_cronog_capacitacion`;
CREATE TABLE "tbl_cronog_capacitacion" (
  "id_cronograma_capacitacion" int NOT NULL AUTO_INCREMENT,
  "id_capacitacion" int DEFAULT NULL COMMENT 'Campo legacy para compatibilidad',
  "nombre_capacitacion" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nuevo sistema: nombre de texto libre',
  "objetivo_capacitacion" text COLLATE utf8mb4_unicode_ci COMMENT 'Nuevo sistema: objetivo de texto libre',
  "id_cliente" int DEFAULT NULL,
  "fecha_programada" date DEFAULT NULL,
  "fecha_de_realizacion" date DEFAULT NULL,
  "estado" enum('PROGRAMADA','EJECUTADA','CANCELADA POR EL CLIENTE','REPROGRAMADA','CERRADA POR FIN CONTRATO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PROGRAMADA',
  "perfil_de_asistentes" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "nombre_del_capacitador" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "horas_de_duracion_de_la_capacitacion" int DEFAULT NULL,
  "indicador_de_realizacion_de_la_capacitacion" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "numero_de_asistentes_a_capacitacion" int DEFAULT NULL,
  "numero_total_de_personas_programadas" int DEFAULT NULL,
  "porcentaje_cobertura" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "numero_de_personas_evaluadas" int DEFAULT NULL,
  "promedio_de_calificaciones" decimal(5,2) DEFAULT NULL,
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "id_reporte_capacitacion" int DEFAULT NULL,
  PRIMARY KEY ("id_cronograma_capacitacion"),
  KEY "idx_cliente" ("id_cliente"),
  KEY "idx_fecha_programada" ("fecha_programada"),
  KEY "idx_estado" ("estado"),
  KEY "idx_cronog_reporte" ("id_reporte_capacitacion")
);

-- Tabla: tbl_cronog_capacitacion_old
DROP TABLE IF EXISTS `tbl_cronog_capacitacion_old`;
CREATE TABLE "tbl_cronog_capacitacion_old" (
  "id_cronograma_capacitacion" int NOT NULL AUTO_INCREMENT,
  "id_capacitacion" int NOT NULL,
  "id_cliente" int NOT NULL,
  "fecha_programada" date DEFAULT NULL,
  "fecha_de_realizacion" date DEFAULT NULL,
  "estado" varchar(255) NOT NULL,
  "perfil_de_asistentes" varchar(255) DEFAULT NULL,
  "nombre_del_capacitador" varchar(255) DEFAULT NULL,
  "horas_de_duracion_de_la_capacitacion" int DEFAULT NULL,
  "indicador_de_realizacion_de_la_capacitacion" varchar(255) DEFAULT NULL,
  "numero_de_asistentes_a_capacitacion" int DEFAULT NULL,
  "numero_total_de_personas_programadas" int DEFAULT NULL,
  "porcentaje_cobertura" varchar(255) DEFAULT NULL,
  "numero_de_personas_evaluadas" int DEFAULT NULL,
  "promedio_de_calificaciones" decimal(5,2) DEFAULT NULL,
  "observaciones" text,
  PRIMARY KEY ("id_cronograma_capacitacion"),
  KEY "id_capacitacion" ("id_capacitacion"),
  KEY "id_cliente" ("id_cliente"),
  CONSTRAINT "tbl_cronog_capacitacion_old_ibfk_1" FOREIGN KEY ("id_capacitacion") REFERENCES "capacitaciones_sst" ("id_capacitacion") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "tbl_cronog_capacitacion_old_ibfk_2" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabla: tbl_data_owner
DROP TABLE IF EXISTS `tbl_data_owner`;
CREATE TABLE "tbl_data_owner" (
  "id_data_owner" int NOT NULL AUTO_INCREMENT,
  "data_owner" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "comment_data_owner" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "created_at" timestamp NULL DEFAULT NULL,
  "updated_at" timestamp NULL DEFAULT NULL,
  PRIMARY KEY ("id_data_owner"),
  UNIQUE KEY "id_data_owner" ("id_data_owner")
);

-- Tabla: tbl_dotacion_aseadora
DROP TABLE IF EXISTS `tbl_dotacion_aseadora`;
CREATE TABLE "tbl_dotacion_aseadora" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_inspeccion" date NOT NULL,
  "contratista" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "servicio" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "nombre_cargo" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "actividades_frecuentes" text COLLATE utf8mb4_unicode_ci,
  "foto_cuerpo_completo" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_cuarto_almacenamiento" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_tapabocas" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_guantes_nitrilo" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_guantes_caucho" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_gafas" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_uniforme" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_sombrero" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_zapato" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_botas_caucho" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "concepto_final" text COLLATE utf8mb4_unicode_ci,
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_dot_ase_cliente" ("id_cliente"),
  KEY "idx_dot_ase_consultor" ("id_consultor"),
  KEY "idx_dot_ase_estado" ("estado"),
  CONSTRAINT "fk_dot_ase_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_dotacion_todero
DROP TABLE IF EXISTS `tbl_dotacion_todero`;
CREATE TABLE "tbl_dotacion_todero" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_inspeccion" date NOT NULL,
  "contratista" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "servicio" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "nombre_cargo" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "actividades_frecuentes" text COLLATE utf8mb4_unicode_ci,
  "foto_cuerpo_completo" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_cuarto_almacenamiento" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_tapabocas" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_guantes_nitrilo" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_mascarilla_polvo" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_guantes_nylon" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_guantes_caucho" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_gafas" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_uniforme" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_sombrero" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_zapato" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_casco" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_careta" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_protector_auditivo" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_respirador" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_guantes_vaqueta" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_botas_dielectricas" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_delantal_pvc" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "concepto_final" text COLLATE utf8mb4_unicode_ci,
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_dot_tod_cliente" ("id_cliente"),
  KEY "idx_dot_tod_consultor" ("id_consultor"),
  KEY "idx_dot_tod_estado" ("estado"),
  CONSTRAINT "fk_dot_tod_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_dotacion_vigilante
DROP TABLE IF EXISTS `tbl_dotacion_vigilante`;
CREATE TABLE "tbl_dotacion_vigilante" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_inspeccion" date NOT NULL,
  "contratista" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "servicio" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "nombre_cargo" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "actividades_frecuentes" text COLLATE utf8mb4_unicode_ci,
  "foto_cuerpo_completo" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_cuarto_almacenamiento" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_uniforme" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_chaqueta" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_radio" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_baston" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_arma" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_calzado" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_gorra" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_carne" enum('bueno','regular','deficiente','no_tiene','no_aplica') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "concepto_final" text COLLATE utf8mb4_unicode_ci,
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_dot_vig_cliente" ("id_cliente"),
  KEY "idx_dot_vig_consultor" ("id_consultor"),
  KEY "idx_dot_vig_estado" ("estado"),
  CONSTRAINT "fk_dot_vig_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_elemento_botiquin
DROP TABLE IF EXISTS `tbl_elemento_botiquin`;
CREATE TABLE "tbl_elemento_botiquin" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_inspeccion" int NOT NULL,
  "clave" varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Key del ELEMENTOS constant',
  "cantidad" int NOT NULL DEFAULT '0',
  "estado" varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BUEN ESTADO' COMMENT 'BUEN ESTADO, ESTADO REGULAR, MAL ESTADO, SIN EXISTENCIAS, VENCIDO, NO APLICA',
  "fecha_vencimiento" date DEFAULT NULL COMMENT 'Solo para items con vencimiento',
  "orden" tinyint NOT NULL DEFAULT '0',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_elem_bot_inspeccion" ("id_inspeccion"),
  CONSTRAINT "fk_elem_bot_inspeccion" FOREIGN KEY ("id_inspeccion") REFERENCES "tbl_inspeccion_botiquin" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabla: tbl_evaluacion_opcion
DROP TABLE IF EXISTS `tbl_evaluacion_opcion`;
CREATE TABLE "tbl_evaluacion_opcion" (
  "id" int unsigned NOT NULL AUTO_INCREMENT,
  "id_pregunta" int unsigned NOT NULL,
  "letra" char(1) NOT NULL COMMENT 'a, b, c, d',
  "texto" text NOT NULL,
  PRIMARY KEY ("id"),
  KEY "idx_pregunta" ("id_pregunta")
);

-- Tabla: tbl_evaluacion_pregunta
DROP TABLE IF EXISTS `tbl_evaluacion_pregunta`;
CREATE TABLE "tbl_evaluacion_pregunta" (
  "id" int unsigned NOT NULL AUTO_INCREMENT,
  "id_tema" int unsigned NOT NULL,
  "orden" tinyint unsigned NOT NULL DEFAULT '0',
  "texto" text NOT NULL,
  "correcta" char(1) NOT NULL COMMENT 'Letra de la opcion correcta: a,b,c,d',
  "created_at" datetime DEFAULT NULL,
  "updated_at" datetime DEFAULT NULL,
  PRIMARY KEY ("id"),
  KEY "idx_tema_orden" ("id_tema","orden")
);

-- Tabla: tbl_evaluacion_respuestas
DROP TABLE IF EXISTS `tbl_evaluacion_respuestas`;
CREATE TABLE "tbl_evaluacion_respuestas" (
  "id" int unsigned NOT NULL AUTO_INCREMENT,
  "id_evaluacion" int unsigned NOT NULL,
  "nombre" varchar(255) NOT NULL,
  "cedula" varchar(30) NOT NULL,
  "whatsapp" varchar(30) NOT NULL DEFAULT '',
  "empresa_contratante" varchar(255) NOT NULL DEFAULT '',
  "cargo" varchar(100) NOT NULL DEFAULT '',
  "id_cliente_conjunto" int unsigned DEFAULT NULL,
  "acepta_tratamiento" tinyint(1) NOT NULL DEFAULT '0',
  "respuestas" json DEFAULT NULL,
  "calificacion" decimal(5,2) NOT NULL DEFAULT '0.00',
  "created_at" datetime DEFAULT NULL,
  "updated_at" datetime DEFAULT NULL,
  PRIMARY KEY ("id"),
  KEY "idx_id_evaluacion" ("id_evaluacion")
);

-- Tabla: tbl_evaluacion_sesiones
DROP TABLE IF EXISTS `tbl_evaluacion_sesiones`;
CREATE TABLE "tbl_evaluacion_sesiones" (
  "id" int unsigned NOT NULL AUTO_INCREMENT,
  "id_evaluacion" int unsigned NOT NULL,
  "id_cliente" int unsigned NOT NULL,
  "fecha_sesion" date NOT NULL,
  "codigo" varchar(20) NOT NULL,
  "created_at" datetime DEFAULT NULL,
  "updated_at" datetime DEFAULT NULL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "uk_codigo" ("codigo"),
  UNIQUE KEY "uk_sesion" ("id_evaluacion","id_cliente","fecha_sesion")
);

-- Tabla: tbl_evaluacion_simulacro
DROP TABLE IF EXISTS `tbl_evaluacion_simulacro`;
CREATE TABLE "tbl_evaluacion_simulacro" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "fecha" date NOT NULL,
  "direccion" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "evento_simulado" enum('Sismo','Incendio','Evacuación') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "alcance_simulacro" enum('Total','Parcial') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "tipo_evacuacion" varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "personal_no_evacua" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "tipo_alarma" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Valores separados por coma',
  "distintivos_brigadistas" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Valores separados por coma',
  "puntos_encuentro" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "recurso_humano" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "equipos_emergencia" text COLLATE utf8mb4_unicode_ci COMMENT 'Valores separados por coma',
  "nombre_brigadista_lider" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "email_brigadista_lider" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "whatsapp_brigadista_lider" varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "imagen_1" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "imagen_2" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "hora_inicio" datetime DEFAULT NULL,
  "alistamiento_recursos" datetime DEFAULT NULL,
  "asumir_roles" datetime DEFAULT NULL,
  "suena_alarma" datetime DEFAULT NULL,
  "distribucion_roles" datetime DEFAULT NULL,
  "llegada_punto_encuentro" datetime DEFAULT NULL,
  "agrupacion_por_afinidad" datetime DEFAULT NULL,
  "conteo_personal" datetime DEFAULT NULL,
  "agradecimiento_y_cierre" datetime DEFAULT NULL,
  "tiempo_total" varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'HH:MM:SS calculado',
  "alarma_efectiva" tinyint DEFAULT NULL,
  "orden_evacuacion" tinyint DEFAULT NULL,
  "liderazgo_brigadistas" tinyint DEFAULT NULL,
  "organizacion_punto_encuentro" tinyint DEFAULT NULL,
  "participacion_general" tinyint DEFAULT NULL,
  "evaluacion_cuantitativa" varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ej: 8.2/10',
  "evaluacion_cualitativa" text COLLATE utf8mb4_unicode_ci,
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "hombre" int NOT NULL DEFAULT '0',
  "mujer" int NOT NULL DEFAULT '0',
  "ninos" int NOT NULL DEFAULT '0',
  "adultos_mayores" int NOT NULL DEFAULT '0',
  "discapacidad" int NOT NULL DEFAULT '0',
  "mascotas" int NOT NULL DEFAULT '0',
  "total" int NOT NULL DEFAULT '0',
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_eval_sim_cliente" ("id_cliente"),
  KEY "idx_eval_sim_estado" ("estado"),
  KEY "idx_eval_sim_fecha" ("fecha"),
  CONSTRAINT "fk_eval_sim_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_evaluacion_tema
DROP TABLE IF EXISTS `tbl_evaluacion_tema`;
CREATE TABLE "tbl_evaluacion_tema" (
  "id" int unsigned NOT NULL AUTO_INCREMENT,
  "nombre" varchar(200) NOT NULL,
  "descripcion" text,
  "estado" enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  "created_at" datetime DEFAULT NULL,
  "updated_at" datetime DEFAULT NULL,
  PRIMARY KEY ("id")
);

-- Tabla: tbl_evaluaciones
DROP TABLE IF EXISTS `tbl_evaluaciones`;
CREATE TABLE "tbl_evaluaciones" (
  "id" int unsigned NOT NULL AUTO_INCREMENT,
  "id_asistencia_induccion" int unsigned DEFAULT NULL,
  "id_cliente" int unsigned DEFAULT NULL,
  "id_tema" int unsigned DEFAULT NULL,
  "titulo" varchar(255) NOT NULL DEFAULT 'Evaluación Inducción SST',
  "token" varchar(64) NOT NULL,
  "estado" enum('activo','cerrado') NOT NULL DEFAULT 'activo',
  "created_at" datetime DEFAULT NULL,
  "updated_at" datetime DEFAULT NULL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "uq_token" ("token")
);

-- Tabla: tbl_extintor_detalle
DROP TABLE IF EXISTS `tbl_extintor_detalle`;
CREATE TABLE "tbl_extintor_detalle" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_inspeccion" int NOT NULL,
  "pintura_cilindro" varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BUENO',
  "golpes_extintor" varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NO',
  "autoadhesivo" varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BUENO',
  "manija_transporte" varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BUENO',
  "palanca_accionamiento" varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BUENO',
  "presion" varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'CARGADO',
  "manometro" varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BUENO',
  "boquilla" varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BUENO',
  "manguera" varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NO APLICA',
  "ring_seguridad" varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BUENO',
  "senalizacion" varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BUENO',
  "soporte" varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BUENO',
  "fecha_vencimiento" date DEFAULT NULL,
  "foto" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta foto evidencia',
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "orden" tinyint NOT NULL DEFAULT '0',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_ext_det_inspeccion" ("id_inspeccion"),
  CONSTRAINT "fk_ext_det_inspeccion" FOREIGN KEY ("id_inspeccion") REFERENCES "tbl_inspeccion_extintores" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabla: tbl_gabinete_detalle
DROP TABLE IF EXISTS `tbl_gabinete_detalle`;
CREATE TABLE "tbl_gabinete_detalle" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_inspeccion" int NOT NULL,
  "numero" int NOT NULL DEFAULT '1',
  "ubicacion" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "tiene_manguera" enum('SI','NO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SI',
  "tiene_hacha" enum('SI','NO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SI',
  "tiene_extintor" enum('SI','NO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NO',
  "tiene_valvula" enum('SI','NO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SI',
  "tiene_boquilla" enum('SI','NO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SI',
  "tiene_llave_spanner" enum('SI','NO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NO',
  "estado" varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BUENO',
  "senalizacion" varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BUENO',
  "foto" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta foto evidencia',
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_gab_det_inspeccion" ("id_inspeccion"),
  CONSTRAINT "fk_gab_det_inspeccion" FOREIGN KEY ("id_inspeccion") REFERENCES "tbl_inspeccion_gabinetes" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabla: tbl_hallazgo_locativo
DROP TABLE IF EXISTS `tbl_hallazgo_locativo`;
CREATE TABLE "tbl_hallazgo_locativo" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_inspeccion" int NOT NULL,
  "descripcion" text COLLATE utf8mb4_unicode_ci NOT NULL,
  "imagen" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta foto del hallazgo',
  "imagen_correccion" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta foto de corrección',
  "fecha_hallazgo" date DEFAULT NULL,
  "fecha_correccion" date DEFAULT NULL,
  "estado" varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ABIERTO' COMMENT 'ABIERTO, CERRADO, TIEMPO EXCEDIDO SIN RESPUESTA',
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "orden" tinyint NOT NULL DEFAULT '0',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_hallazgo_inspeccion" ("id_inspeccion"),
  KEY "idx_hallazgo_estado" ("estado"),
  CONSTRAINT "fk_hallazgo_inspeccion" FOREIGN KEY ("id_inspeccion") REFERENCES "tbl_inspeccion_locativa" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabla: tbl_hv_brigadista
DROP TABLE IF EXISTS `tbl_hv_brigadista`;
CREATE TABLE "tbl_hv_brigadista" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "fecha_registro" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "fecha_inscripcion" date DEFAULT NULL,
  "foto_brigadista" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Path a foto del brigadista',
  "nombre_completo" varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  "documento_identidad" varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  "f_nacimiento" date DEFAULT NULL,
  "email" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "telefono" varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "direccion_residencia" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "edad" int DEFAULT NULL,
  "eps" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "peso" decimal(5,1) DEFAULT NULL,
  "estatura" decimal(5,1) DEFAULT NULL,
  "rh" varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'O+, O-, A+, A-, B+, B-, AB+, AB-',
  "estudios_1" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "lugar_estudio_1" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "anio_estudio_1" int DEFAULT NULL,
  "estudios_2" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "lugar_estudio_2" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "anio_estudio_2" int DEFAULT NULL,
  "estudios_3" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "lugar_estudio_3" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "anio_estudio_3" int DEFAULT NULL,
  "enfermedades_importantes" text COLLATE utf8mb4_unicode_ci,
  "medicamentos" text COLLATE utf8mb4_unicode_ci,
  "cardiaca" enum('SI','NO') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "pechoactividad" enum('SI','NO') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "dolorpecho" enum('SI','NO') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "conciencia" enum('SI','NO') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "huesos" enum('SI','NO') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "medicamentos_bool" enum('SI','NO') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "actividadfisica" enum('SI','NO') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "convulsiones" enum('SI','NO') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "vertigo" enum('SI','NO') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "oidos" enum('SI','NO') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "lugarescerrados" enum('SI','NO') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "miedoalturas" enum('SI','NO') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "haceejercicio" enum('SI','NO') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "miedo_ver_sangre" enum('SI','NO') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "restricciones_medicas" text COLLATE utf8mb4_unicode_ci,
  "deporte_semana" text COLLATE utf8mb4_unicode_ci,
  "firma" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Path a imagen de firma',
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "ruta_pdf" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_hv_brig_cliente" ("id_cliente"),
  KEY "idx_hv_brig_estado" ("estado"),
  KEY "idx_hv_brig_doc" ("documento_identidad"),
  CONSTRAINT "fk_hv_brig_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_informe_avances
DROP TABLE IF EXISTS `tbl_informe_avances`;
CREATE TABLE "tbl_informe_avances" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_desde" date NOT NULL,
  "fecha_hasta" date NOT NULL,
  "anio" smallint NOT NULL,
  "puntaje_anterior" decimal(5,2) DEFAULT NULL,
  "puntaje_actual" decimal(5,2) DEFAULT NULL,
  "diferencia_neta" decimal(5,2) DEFAULT NULL,
  "estado_avance" varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ESTABLE',
  "indicador_plan_trabajo" decimal(5,2) DEFAULT NULL,
  "indicador_capacitacion" decimal(5,2) DEFAULT NULL,
  "img_cumplimiento_estandares" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "img_indicador_plan_trabajo" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "img_indicador_capacitacion" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "metricas_desglose_json" longtext COLLATE utf8mb4_unicode_ci,
  "resumen_avance" longtext COLLATE utf8mb4_unicode_ci,
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "actividades_abiertas" text COLLATE utf8mb4_unicode_ci,
  "actividades_cerradas_periodo" text COLLATE utf8mb4_unicode_ci,
  "enlace_dashboard" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "acta_visita_url" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "soporte_1_texto" text COLLATE utf8mb4_unicode_ci,
  "soporte_1_imagen" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "soporte_2_texto" text COLLATE utf8mb4_unicode_ci,
  "soporte_2_imagen" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "soporte_3_texto" text COLLATE utf8mb4_unicode_ci,
  "soporte_3_imagen" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "soporte_4_texto" text COLLATE utf8mb4_unicode_ci,
  "soporte_4_imagen" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "ruta_pdf" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_infavance_cliente" ("id_cliente"),
  KEY "idx_infavance_consultor" ("id_consultor"),
  KEY "idx_infavance_estado" ("estado"),
  KEY "idx_infavance_fecha" ("fecha_hasta"),
  CONSTRAINT "fk_infavance_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT
);

-- Tabla: tbl_inspeccion_botiquin
DROP TABLE IF EXISTS `tbl_inspeccion_botiquin`;
CREATE TABLE "tbl_inspeccion_botiquin" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_inspeccion" date NOT NULL,
  "ubicacion_botiquin" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_1" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Foto general del botiquin',
  "foto_2" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Foto general del botiquin 2',
  "instalado_pared" enum('SI','NO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SI',
  "libre_obstaculos" enum('SI','NO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SI',
  "lugar_visible" enum('SI','NO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SI',
  "con_senalizacion" enum('SI','NO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SI',
  "tipo_botiquin" varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'LONA' COMMENT 'LONA, METALICO',
  "estado_botiquin" varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BUEN ESTADO' COMMENT 'BUEN ESTADO, ESTADO REGULAR, MAL ESTADO',
  "foto_tabla_espinal" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "obs_tabla_espinal" text COLLATE utf8mb4_unicode_ci,
  "estado_collares" varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'BUEN ESTADO',
  "foto_collares" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado_inmovilizadores" varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'BUEN ESTADO',
  "foto_inmovilizadores" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "recomendaciones" text COLLATE utf8mb4_unicode_ci,
  "pendientes_generados" text COLLATE utf8mb4_unicode_ci COMMENT 'Auto-calculado al finalizar',
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_insp_bot_cliente" ("id_cliente"),
  KEY "idx_insp_bot_consultor" ("id_consultor"),
  KEY "idx_insp_bot_estado" ("estado"),
  CONSTRAINT "fk_insp_bot_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_inspeccion_comunicaciones
DROP TABLE IF EXISTS `tbl_inspeccion_comunicaciones`;
CREATE TABLE "tbl_inspeccion_comunicaciones" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_inspeccion" date NOT NULL,
  "cant_telefono_fijo" int NOT NULL DEFAULT '0',
  "obs_telefono_fijo" text COLLATE utf8mb4_unicode_ci,
  "cant_telefonia_celular" int NOT NULL DEFAULT '0',
  "obs_telefonia_celular" text COLLATE utf8mb4_unicode_ci,
  "cant_radio_onda_corta" int NOT NULL DEFAULT '0',
  "obs_radio_onda_corta" text COLLATE utf8mb4_unicode_ci,
  "cant_software_citofonia" int NOT NULL DEFAULT '0',
  "obs_software_citofonia" text COLLATE utf8mb4_unicode_ci,
  "cant_megafonia" int NOT NULL DEFAULT '0',
  "obs_megafonia" text COLLATE utf8mb4_unicode_ci,
  "cant_cctv_audio" int NOT NULL DEFAULT '0',
  "obs_cctv_audio" text COLLATE utf8mb4_unicode_ci,
  "cant_alarma_comunicacion" int NOT NULL DEFAULT '0',
  "obs_alarma_comunicacion" text COLLATE utf8mb4_unicode_ci,
  "cant_voip" int NOT NULL DEFAULT '0',
  "obs_voip" text COLLATE utf8mb4_unicode_ci,
  "foto_1" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_2" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "observaciones_finales" text COLLATE utf8mb4_unicode_ci,
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_insp_com_cliente" ("id_cliente"),
  KEY "idx_insp_com_consultor" ("id_consultor"),
  KEY "idx_insp_com_estado" ("estado"),
  CONSTRAINT "fk_insp_com_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_inspeccion_extintores
DROP TABLE IF EXISTS `tbl_inspeccion_extintores`;
CREATE TABLE "tbl_inspeccion_extintores" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_inspeccion" date NOT NULL,
  "fecha_vencimiento_global" date DEFAULT NULL,
  "numero_extintores_totales" int NOT NULL DEFAULT '0',
  "cantidad_abc" int NOT NULL DEFAULT '0',
  "cantidad_co2" int NOT NULL DEFAULT '0',
  "cantidad_solkaflam" int NOT NULL DEFAULT '0',
  "cantidad_agua" int NOT NULL DEFAULT '0',
  "capacidad_libras" varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ej: 10 LIBRAS',
  "cantidad_unidades_residenciales" int NOT NULL DEFAULT '0',
  "cantidad_porteria" int NOT NULL DEFAULT '0',
  "cantidad_oficina_admin" int NOT NULL DEFAULT '0',
  "cantidad_shut_basuras" int NOT NULL DEFAULT '0',
  "cantidad_salones_comunales" int NOT NULL DEFAULT '0',
  "cantidad_cuarto_bombas" int NOT NULL DEFAULT '0',
  "cantidad_planta_electrica" int NOT NULL DEFAULT '0',
  "recomendaciones_generales" text COLLATE utf8mb4_unicode_ci,
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_insp_ext_cliente" ("id_cliente"),
  KEY "idx_insp_ext_consultor" ("id_consultor"),
  KEY "idx_insp_ext_estado" ("estado"),
  CONSTRAINT "fk_insp_ext_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_inspeccion_gabinetes
DROP TABLE IF EXISTS `tbl_inspeccion_gabinetes`;
CREATE TABLE "tbl_inspeccion_gabinetes" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_inspeccion" date NOT NULL,
  "tiene_gabinetes" enum('SI','NO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SI',
  "entregados_constructora" enum('SI','NO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SI',
  "cantidad_gabinetes" int NOT NULL DEFAULT '0',
  "elementos_gabinete" text COLLATE utf8mb4_unicode_ci COMMENT 'Elementos que contiene cada gabinete',
  "ubicacion_gabinetes" text COLLATE utf8mb4_unicode_ci COMMENT 'Ubicación detallada de los gabinetes',
  "estado_senalizacion_gab" text COLLATE utf8mb4_unicode_ci COMMENT 'Estado de la señalización',
  "foto_gab_1" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_gab_2" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "observaciones_gabinetes" text COLLATE utf8mb4_unicode_ci,
  "tiene_detectores" enum('SI','NO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SI',
  "detectores_entregados" enum('SI','NO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SI',
  "cantidad_detectores" int NOT NULL DEFAULT '0',
  "ubicacion_detectores" text COLLATE utf8mb4_unicode_ci,
  "foto_det_1" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_det_2" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "observaciones_detectores" text COLLATE utf8mb4_unicode_ci,
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_insp_gab_cliente" ("id_cliente"),
  KEY "idx_insp_gab_consultor" ("id_consultor"),
  KEY "idx_insp_gab_estado" ("estado"),
  CONSTRAINT "fk_insp_gab_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_inspeccion_locativa
DROP TABLE IF EXISTS `tbl_inspeccion_locativa`;
CREATE TABLE "tbl_inspeccion_locativa" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_inspeccion" date NOT NULL,
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_insp_loc_cliente" ("id_cliente"),
  KEY "idx_insp_loc_consultor" ("id_consultor"),
  KEY "idx_insp_loc_estado" ("estado"),
  CONSTRAINT "fk_insp_locativa_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_inspeccion_recursos_seguridad
DROP TABLE IF EXISTS `tbl_inspeccion_recursos_seguridad`;
CREATE TABLE "tbl_inspeccion_recursos_seguridad" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_inspeccion" date NOT NULL,
  "obs_lamparas" text COLLATE utf8mb4_unicode_ci,
  "foto_lamparas" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "obs_antideslizantes" text COLLATE utf8mb4_unicode_ci,
  "foto_antideslizantes" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "obs_pasamanos" text COLLATE utf8mb4_unicode_ci,
  "foto_pasamanos" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "obs_vigilancia" text COLLATE utf8mb4_unicode_ci,
  "foto_vigilancia" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "obs_iluminacion" text COLLATE utf8mb4_unicode_ci,
  "foto_iluminacion" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "obs_planes_respuesta" text COLLATE utf8mb4_unicode_ci,
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_insp_rec_cliente" ("id_cliente"),
  KEY "idx_insp_rec_consultor" ("id_consultor"),
  KEY "idx_insp_rec_estado" ("estado"),
  CONSTRAINT "fk_insp_rec_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_inspeccion_senalizacion
DROP TABLE IF EXISTS `tbl_inspeccion_senalizacion`;
CREATE TABLE "tbl_inspeccion_senalizacion" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_inspeccion" date NOT NULL,
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "calificacion" decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'Porcentaje 0-100',
  "descripcion_cualitativa" varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Nivel crítico/bajo/medio/bueno/excelente',
  "conteo_no_aplica" int NOT NULL DEFAULT '0',
  "conteo_no_cumple" int NOT NULL DEFAULT '0',
  "conteo_parcial" int NOT NULL DEFAULT '0',
  "conteo_total" int NOT NULL DEFAULT '0',
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_insp_senal_cliente" ("id_cliente"),
  KEY "idx_insp_senal_consultor" ("id_consultor"),
  KEY "idx_insp_senal_estado" ("estado"),
  CONSTRAINT "fk_insp_senal_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_inventario_actividades_plandetrabajo
DROP TABLE IF EXISTS `tbl_inventario_actividades_plandetrabajo`;
CREATE TABLE "tbl_inventario_actividades_plandetrabajo" (
  "id_inventario_actividades_plandetrabajo" int NOT NULL AUTO_INCREMENT,
  "phva_plandetrabajo" varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "numeral_plandetrabajo" varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "actividad_plandetrabajo" text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "responsable_sugerido_plandetrabajo" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY ("id_inventario_actividades_plandetrabajo")
);

-- Tabla: tbl_item_senalizacion
DROP TABLE IF EXISTS `tbl_item_senalizacion`;
CREATE TABLE "tbl_item_senalizacion" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_inspeccion" int NOT NULL,
  "nombre_item" varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre del ítem de señalización',
  "grupo" varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Categoría/grupo del ítem',
  "estado_cumplimiento" varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NO CUMPLE' COMMENT 'NO APLICA, NO CUMPLE, CUMPLE PARCIALMENTE, CUMPLE TOTALMENTE',
  "foto" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta foto evidencia',
  "orden" tinyint NOT NULL DEFAULT '0',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_item_senal_inspeccion" ("id_inspeccion"),
  KEY "idx_item_senal_estado" ("estado_cumplimiento"),
  CONSTRAINT "fk_item_senal_inspeccion" FOREIGN KEY ("id_inspeccion") REFERENCES "tbl_inspeccion_senalizacion" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabla: tbl_kpi_agua_potable
DROP TABLE IF EXISTS `tbl_kpi_agua_potable`;
CREATE TABLE "tbl_kpi_agua_potable" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_inspeccion" date NOT NULL,
  "nombre_responsable" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "indicador" varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  "cumplimiento" decimal(5,2) NOT NULL DEFAULT '0.00',
  "valor_numerador" int DEFAULT NULL,
  "valor_denominador" int DEFAULT NULL,
  "calificacion_cualitativa" varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "registro_formato_1" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "registro_formato_2" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "registro_formato_3" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "registro_formato_4" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "ruta_pdf" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "fk_kpi_agua_cliente" ("id_cliente"),
  KEY "fk_kpi_agua_consultor" ("id_consultor"),
  CONSTRAINT "fk_kpi_agua_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE CASCADE
);

-- Tabla: tbl_kpi_definition
DROP TABLE IF EXISTS `tbl_kpi_definition`;
CREATE TABLE "tbl_kpi_definition" (
  "id_kpi_definition" int NOT NULL AUTO_INCREMENT,
  "name_kpi_definition" varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "comment_kpi_definition" varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "created_at" timestamp NULL DEFAULT NULL,
  "updated_at" timestamp NULL DEFAULT NULL,
  PRIMARY KEY ("id_kpi_definition"),
  UNIQUE KEY "id_kpi_definition" ("id_kpi_definition")
);

-- Tabla: tbl_kpi_limpieza
DROP TABLE IF EXISTS `tbl_kpi_limpieza`;
CREATE TABLE "tbl_kpi_limpieza" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_inspeccion" date NOT NULL,
  "nombre_responsable" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "indicador" varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  "cumplimiento" decimal(5,2) NOT NULL DEFAULT '0.00',
  "valor_numerador" int DEFAULT NULL,
  "valor_denominador" int DEFAULT NULL,
  "calificacion_cualitativa" varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "registro_formato_1" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "registro_formato_2" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "registro_formato_3" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "registro_formato_4" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "ruta_pdf" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "fk_kpi_limp_cliente" ("id_cliente"),
  KEY "fk_kpi_limp_consultor" ("id_consultor"),
  CONSTRAINT "fk_kpi_limp_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE CASCADE
);

-- Tabla: tbl_kpi_plagas
DROP TABLE IF EXISTS `tbl_kpi_plagas`;
CREATE TABLE "tbl_kpi_plagas" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_inspeccion" date NOT NULL,
  "nombre_responsable" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "indicador" varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  "cumplimiento" decimal(5,2) NOT NULL DEFAULT '0.00',
  "valor_numerador" int DEFAULT NULL,
  "valor_denominador" int DEFAULT NULL,
  "calificacion_cualitativa" varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "registro_formato_1" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "registro_formato_2" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "registro_formato_3" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "registro_formato_4" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "ruta_pdf" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "fk_kpi_plag_cliente" ("id_cliente"),
  KEY "fk_kpi_plag_consultor" ("id_consultor"),
  CONSTRAINT "fk_kpi_plag_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE CASCADE
);

-- Tabla: tbl_kpi_policy
DROP TABLE IF EXISTS `tbl_kpi_policy`;
CREATE TABLE "tbl_kpi_policy" (
  "id_kpi_policy" int NOT NULL AUTO_INCREMENT,
  "policy_kpi_definition" varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "policy_kpi_comments" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "created_at" timestamp NULL DEFAULT NULL,
  "updated_at" timestamp NULL DEFAULT NULL,
  PRIMARY KEY ("id_kpi_policy"),
  UNIQUE KEY "id_kpi_policy" ("id_kpi_policy")
);

-- Tabla: tbl_kpi_residuos
DROP TABLE IF EXISTS `tbl_kpi_residuos`;
CREATE TABLE "tbl_kpi_residuos" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_inspeccion" date NOT NULL,
  "nombre_responsable" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "indicador" varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  "cumplimiento" decimal(5,2) NOT NULL DEFAULT '0.00',
  "valor_numerador" int DEFAULT NULL,
  "valor_denominador" int DEFAULT NULL,
  "calificacion_cualitativa" varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "registro_formato_1" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "registro_formato_2" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "registro_formato_3" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "registro_formato_4" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "ruta_pdf" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "fk_kpi_res_cliente" ("id_cliente"),
  KEY "fk_kpi_res_consultor" ("id_consultor"),
  CONSTRAINT "fk_kpi_res_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE CASCADE
);

-- Tabla: tbl_kpi_type
DROP TABLE IF EXISTS `tbl_kpi_type`;
CREATE TABLE "tbl_kpi_type" (
  "id_kpi_type" int NOT NULL AUTO_INCREMENT,
  "kpi_type" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "kpi_type_comment" varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "created_at" timestamp NULL DEFAULT NULL,
  "updated_at" timestamp NULL DEFAULT NULL,
  PRIMARY KEY ("id_kpi_type"),
  UNIQUE KEY "id_kpi_type" ("id_kpi_type")
);

-- Tabla: tbl_kpis
DROP TABLE IF EXISTS `tbl_kpis`;
CREATE TABLE "tbl_kpis" (
  "id_kpis" int NOT NULL AUTO_INCREMENT,
  "kpi_name" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "hpi_comments" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "created_at" timestamp NULL DEFAULT NULL,
  "updated_at" timestamp NULL DEFAULT NULL,
  PRIMARY KEY ("id_kpis"),
  UNIQUE KEY "id_kpis" ("id_kpis")
);

-- Tabla: tbl_listado_maestro_documentos
DROP TABLE IF EXISTS `tbl_listado_maestro_documentos`;
CREATE TABLE "tbl_listado_maestro_documentos" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tipo_documento" varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FORMATO, MANUAL, POLITICA, PROCEDIMIENTO, PROGRAMA, REGLAMENTO, MATRICES',
  "codigo" varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  "nombre_documento" varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  "version" varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT '001',
  "ubicacion" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'Dashboard Enterprisesst',
  "fecha" date DEFAULT NULL,
  "estado" enum('Vigente','Obsoleto','En revision') COLLATE utf8mb4_unicode_ci DEFAULT 'Vigente',
  "control_cambios" text COLLATE utf8mb4_unicode_ci,
  "orden" int DEFAULT '0',
  "activo" tinyint(1) DEFAULT '1',
  "created_at" datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  UNIQUE KEY "uk_codigo" ("codigo")
);

-- Tabla: tbl_log_conteo_dias
DROP TABLE IF EXISTS `tbl_log_conteo_dias`;
CREATE TABLE "tbl_log_conteo_dias" (
  "id_log" int NOT NULL AUTO_INCREMENT,
  "fecha_ejecucion" datetime DEFAULT CURRENT_TIMESTAMP,
  "filas_afectadas" int NOT NULL,
  "observacion" varchar(255) DEFAULT 'Actualización automática desde EVENT',
  PRIMARY KEY ("id_log")
);

-- Tabla: tbl_lookerstudio
DROP TABLE IF EXISTS `tbl_lookerstudio`;
CREATE TABLE "tbl_lookerstudio" (
  "id_looker" int NOT NULL AUTO_INCREMENT,
  "tipodedashboard" varchar(255) NOT NULL,
  "enlace" text NOT NULL,
  "id_cliente" int NOT NULL,
  "created_at" datetime DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id_looker"),
  KEY "id_cliente" ("id_cliente"),
  CONSTRAINT "tbl_lookerstudio_ibfk_1" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente")
);

-- Tabla: tbl_mantenimientos
DROP TABLE IF EXISTS `tbl_mantenimientos`;
CREATE TABLE "tbl_mantenimientos" (
  "id_mantenimiento" int NOT NULL AUTO_INCREMENT,
  "detalle_mantenimiento" varchar(255) NOT NULL,
  PRIMARY KEY ("id_mantenimiento")
);

-- Tabla: tbl_matrices
DROP TABLE IF EXISTS `tbl_matrices`;
CREATE TABLE "tbl_matrices" (
  "id_matriz" int NOT NULL AUTO_INCREMENT,
  "tipo" varchar(255) NOT NULL,
  "descripcion" varchar(255) NOT NULL,
  "observaciones" varchar(255) NOT NULL,
  "enlace" text NOT NULL,
  "id_cliente" int NOT NULL,
  "created_at" datetime DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id_matriz"),
  KEY "id_cliente" ("id_cliente"),
  CONSTRAINT "tbl_matrices_ibfk_1" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente")
);

-- Tabla: tbl_matricescycloid
DROP TABLE IF EXISTS `tbl_matricescycloid`;
CREATE TABLE "tbl_matricescycloid" (
  "id_matrizcycloid" int NOT NULL AUTO_INCREMENT,
  "titulo_matriz" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "Tipo_documento" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "enlace" text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  "observaciones" text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id_matrizcycloid")
);

-- Tabla: tbl_matriz_vulnerabilidad
DROP TABLE IF EXISTS `tbl_matriz_vulnerabilidad`;
CREATE TABLE "tbl_matriz_vulnerabilidad" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_inspeccion" date NOT NULL,
  "c1_plan_evacuacion" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c2_alarma_evacuacion" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c3_ruta_evacuacion" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c4_visitantes_rutas" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c5_puntos_reunion" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c6_puntos_reunion_2" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c7_senalizacion_evacuacion" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c8_rutas_evacuacion" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c9_ruta_principal" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c10_senal_alarma" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c11_sistema_deteccion" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c12_iluminacion" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c13_iluminacion_emergencia" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c14_sistema_contra_incendio" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c15_extintores" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c16_divulgacion_plan" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c17_coordinador_plan" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c18_brigada_emergencia" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c19_simulacros" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c20_entidades_socorro" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c21_ocupantes" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c22_plano_evacuacion" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c23_rutas_circulacion" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c24_puertas_salida" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "c25_estructura_construccion" enum('a','b','c') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_mat_vul_cliente" ("id_cliente"),
  KEY "idx_mat_vul_consultor" ("id_consultor"),
  KEY "idx_mat_vul_estado" ("estado"),
  CONSTRAINT "fk_mat_vul_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_measurement_period
DROP TABLE IF EXISTS `tbl_measurement_period`;
CREATE TABLE "tbl_measurement_period" (
  "id_measurement_period" int NOT NULL AUTO_INCREMENT,
  "measurement_period" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "comment_measurement_period" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "created_at" timestamp NULL DEFAULT NULL,
  "updated_at" timestamp NULL DEFAULT NULL,
  PRIMARY KEY ("id_measurement_period"),
  UNIQUE KEY "id_measurement_period" ("id_measurement_period")
);

-- Tabla: tbl_objectives_policy
DROP TABLE IF EXISTS `tbl_objectives_policy`;
CREATE TABLE "tbl_objectives_policy" (
  "id_objectives" int NOT NULL AUTO_INCREMENT,
  "name_objectives" varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "comments_objectives" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "created_at" timestamp NULL DEFAULT NULL,
  "updated_at" timestamp NULL DEFAULT NULL,
  PRIMARY KEY ("id_objectives"),
  UNIQUE KEY "id_objectives" ("id_objectives")
);

-- Tabla: tbl_pendientes
DROP TABLE IF EXISTS `tbl_pendientes`;
CREATE TABLE "tbl_pendientes" (
  "id_pendientes" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_acta" varchar(255) NOT NULL,
  "responsable" varchar(255) NOT NULL,
  "tarea_actividad" text NOT NULL,
  "fecha_asignacion" datetime DEFAULT CURRENT_TIMESTAMP,
  "fecha_cierre" date DEFAULT NULL,
  "estado" enum('ABIERTA','CERRADA','SIN RESPUESTA DEL CLIENTE','CERRADA POR FIN CONTRATO') NOT NULL DEFAULT 'ABIERTA',
  "estado_avance" varchar(255) DEFAULT NULL,
  "evidencia_para_cerrarla" text,
  "conteo_dias" int DEFAULT '0',
  "created_at" datetime DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "id_acta_visita" int DEFAULT NULL COMMENT 'FK al acta de visita que generó este pendiente (nullable)',
  PRIMARY KEY ("id_pendientes"),
  KEY "idx_pendiente_acta" ("id_acta_visita"),
  CONSTRAINT "fk_pendiente_acta_visita" FOREIGN KEY ("id_acta_visita") REFERENCES "tbl_acta_visita" ("id") ON DELETE SET NULL ON UPDATE CASCADE
);

-- Tabla: tbl_plan_contingencia_agua
DROP TABLE IF EXISTS `tbl_plan_contingencia_agua`;
CREATE TABLE "tbl_plan_contingencia_agua" (
  "id" int unsigned NOT NULL AUTO_INCREMENT,
  "id_cliente" int unsigned NOT NULL,
  "id_consultor" int unsigned NOT NULL,
  "fecha_programa" date NOT NULL,
  "nombre_responsable" varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "empresa_carrotanque" text COLLATE utf8mb4_unicode_ci COMMENT 'Proveedor de agua alternativa (carrotanque)',
  "capacidad_reserva" varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Capacidad de reserva en litros',
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "ruta_pdf" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "created_at" datetime DEFAULT NULL,
  "updated_at" datetime DEFAULT NULL,
  PRIMARY KEY ("id")
);

-- Tabla: tbl_plan_contingencia_basura
DROP TABLE IF EXISTS `tbl_plan_contingencia_basura`;
CREATE TABLE "tbl_plan_contingencia_basura" (
  "id" int unsigned NOT NULL AUTO_INCREMENT,
  "id_cliente" int unsigned NOT NULL,
  "id_consultor" int unsigned NOT NULL,
  "fecha_programa" date NOT NULL,
  "nombre_responsable" varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "empresa_aseo" varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombre del prestador del servicio de aseo',
  "horario_recoleccion_actual" varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Días y horario normal de recolección',
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "ruta_pdf" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "created_at" datetime DEFAULT NULL,
  "updated_at" datetime DEFAULT NULL,
  PRIMARY KEY ("id")
);

-- Tabla: tbl_plan_contingencia_plagas
DROP TABLE IF EXISTS `tbl_plan_contingencia_plagas`;
CREATE TABLE "tbl_plan_contingencia_plagas" (
  "id" int unsigned NOT NULL AUTO_INCREMENT,
  "id_cliente" int unsigned NOT NULL,
  "id_consultor" int unsigned NOT NULL,
  "fecha_programa" date NOT NULL,
  "nombre_responsable" varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "empresa_fumigadora" text COLLATE utf8mb4_unicode_ci COMMENT 'Nombre y contacto de la empresa de control de plagas',
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "ruta_pdf" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "created_at" datetime DEFAULT NULL,
  "updated_at" datetime DEFAULT NULL,
  PRIMARY KEY ("id")
);

-- Tabla: tbl_plan_emergencia
DROP TABLE IF EXISTS `tbl_plan_emergencia`;
CREATE TABLE "tbl_plan_emergencia" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_visita" date NOT NULL,
  "foto_fachada" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_panorama" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "casas_o_apartamentos" enum('casas','apartamentos') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "sismo_resistente" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "anio_construccion" smallint DEFAULT NULL,
  "numero_torres" smallint DEFAULT NULL,
  "numero_unidades_habitacionales" smallint DEFAULT NULL,
  "casas_pisos" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_torres_1" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_torres_2" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "parqueaderos_carros_residentes" smallint DEFAULT '0',
  "parqueaderos_carros_visitantes" smallint DEFAULT '0',
  "parqueaderos_motos_residentes" smallint DEFAULT '0',
  "parqueaderos_motos_visitantes" smallint DEFAULT '0',
  "hay_parqueadero_privado" enum('si','no') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_parqueaderos_carros" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_parqueaderos_motos" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "cantidad_salones_comunales" smallint DEFAULT '0',
  "cantidad_locales_comerciales" smallint DEFAULT '0',
  "tiene_oficina_admin" enum('si','no') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_oficina_admin" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "tanque_agua" text COLLATE utf8mb4_unicode_ci,
  "planta_electrica" text COLLATE utf8mb4_unicode_ci,
  "circulacion_vehicular" text COLLATE utf8mb4_unicode_ci,
  "foto_circulacion_vehicular" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "circulacion_peatonal" text COLLATE utf8mb4_unicode_ci,
  "foto_circulacion_peatonal_1" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_circulacion_peatonal_2" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "salidas_emergencia" text COLLATE utf8mb4_unicode_ci,
  "foto_salida_emergencia_1" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_salida_emergencia_2" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "ingresos_peatonales" text COLLATE utf8mb4_unicode_ci,
  "foto_ingresos_peatonales" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "accesos_vehiculares" text COLLATE utf8mb4_unicode_ci,
  "foto_acceso_vehicular_1" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_acceso_vehicular_2" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "concepto_entradas_salidas" text COLLATE utf8mb4_unicode_ci,
  "hidrantes" text COLLATE utf8mb4_unicode_ci,
  "cai_cercano" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "bomberos_cercanos" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "proveedor_vigilancia" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "proveedor_aseo" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "otros_proveedores" text COLLATE utf8mb4_unicode_ci,
  "registro_visitantes_forma" text COLLATE utf8mb4_unicode_ci,
  "registro_visitantes_emergencia" enum('si','no') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "cuenta_megafono" enum('si','no') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "ruta_evacuacion" text COLLATE utf8mb4_unicode_ci,
  "mapa_evacuacion" text COLLATE utf8mb4_unicode_ci,
  "foto_ruta_evacuacion_1" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_ruta_evacuacion_2" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "puntos_encuentro" text COLLATE utf8mb4_unicode_ci,
  "foto_punto_encuentro_1" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_punto_encuentro_2" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "sistema_alarma" text COLLATE utf8mb4_unicode_ci,
  "codigos_alerta" text COLLATE utf8mb4_unicode_ci,
  "energia_emergencia" text COLLATE utf8mb4_unicode_ci,
  "deteccion_fuego" text COLLATE utf8mb4_unicode_ci,
  "vias_transito" text COLLATE utf8mb4_unicode_ci,
  "nombre_administrador" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "horarios_administracion" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "personal_aseo" text COLLATE utf8mb4_unicode_ci,
  "personal_vigilancia" text COLLATE utf8mb4_unicode_ci,
  "ciudad" enum('bogota','soacha') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "cuadrante" varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "tiene_gabinetes_hidraulico" enum('si','no') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "ruta_residuos_solidos" text COLLATE utf8mb4_unicode_ci,
  "empresa_aseo" enum('urbaser_soacha','bogota_limpia','promoambiental','ciudad_limpia','area_limpia','lime') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "servicios_sanitarios" text COLLATE utf8mb4_unicode_ci,
  "frecuencia_basura" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "detalle_mascotas" text COLLATE utf8mb4_unicode_ci,
  "detalle_dependencias" text COLLATE utf8mb4_unicode_ci,
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_plan_emg_cliente" ("id_cliente"),
  KEY "idx_plan_emg_consultor" ("id_consultor"),
  KEY "idx_plan_emg_estado" ("estado"),
  CONSTRAINT "fk_plan_emg_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_plan_saneamiento
DROP TABLE IF EXISTS `tbl_plan_saneamiento`;
CREATE TABLE "tbl_plan_saneamiento" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_programa" date NOT NULL,
  "nombre_responsable" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_plan_san_cliente" ("id_cliente"),
  KEY "idx_plan_san_consultor" ("id_consultor"),
  KEY "idx_plan_san_estado" ("estado"),
  CONSTRAINT "fk_plan_san_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_planilla_ss_inspeccion
DROP TABLE IF EXISTS `tbl_planilla_ss_inspeccion`;
CREATE TABLE "tbl_planilla_ss_inspeccion" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "periodo" varchar(7) NOT NULL COMMENT 'YYYY-MM',
  "archivo" varchar(500) DEFAULT NULL,
  "observaciones" text,
  "id_consultor" int DEFAULT NULL,
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_cliente" ("id_cliente"),
  KEY "idx_periodo" ("periodo")
);

-- Tabla: tbl_planillas_seguridad_social
DROP TABLE IF EXISTS `tbl_planillas_seguridad_social`;
CREATE TABLE "tbl_planillas_seguridad_social" (
  "id" int NOT NULL AUTO_INCREMENT,
  "mes_aportes" varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Período YYYY-MM',
  "archivo_pdf" varchar(255) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Nombre del archivo PDF subido',
  "fecha_cargue" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de carga del archivo',
  "cantidad_envios" int NOT NULL DEFAULT '0' COMMENT 'Cantidad de clientes a los que se envió',
  "fecha_envio" datetime DEFAULT NULL COMMENT 'Fecha del último envío masivo',
  "estado_envio" enum('sin_enviar','enviado') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'sin_enviar' COMMENT 'Estado del envío',
  "notas" text COLLATE utf8mb4_general_ci COMMENT 'Observaciones opcionales',
  PRIMARY KEY ("id")
);

-- Tabla: tbl_preparacion_simulacro
DROP TABLE IF EXISTS `tbl_preparacion_simulacro`;
CREATE TABLE "tbl_preparacion_simulacro" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_simulacro" date NOT NULL,
  "ubicacion" varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "direccion" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "evento_simulado" enum('sismo') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "alcance_simulacro" enum('total','parcial') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "tipo_evacuacion" enum('horizontal','vertical','mixta') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "personal_no_evacua" text COLLATE utf8mb4_unicode_ci,
  "tipo_alarma" text COLLATE utf8mb4_unicode_ci,
  "distintivos_brigadistas" text COLLATE utf8mb4_unicode_ci,
  "puntos_encuentro" text COLLATE utf8mb4_unicode_ci,
  "recurso_humano" text COLLATE utf8mb4_unicode_ci,
  "equipos_emergencia" text COLLATE utf8mb4_unicode_ci,
  "nombre_brigadista_lider" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "email_brigadista_lider" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "whatsapp_brigadista_lider" varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "entrega_formato_evaluacion" enum('si','no') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "imagen_1" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "imagen_2" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "hora_inicio" time DEFAULT NULL,
  "alistamiento_recursos" time DEFAULT NULL,
  "asumir_roles" time DEFAULT NULL,
  "suena_alarma" time DEFAULT NULL,
  "distribucion_roles" time DEFAULT NULL,
  "llegada_punto_encuentro" time DEFAULT NULL,
  "agrupacion_por_afinidad" time DEFAULT NULL,
  "conteo_personal" time DEFAULT NULL,
  "agradecimiento_cierre" time DEFAULT NULL,
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_prep_sim_cliente" ("id_cliente"),
  KEY "idx_prep_sim_consultor" ("id_consultor"),
  KEY "idx_prep_sim_estado" ("estado"),
  CONSTRAINT "fk_prep_sim_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_presupuesto_categorias
DROP TABLE IF EXISTS `tbl_presupuesto_categorias`;
CREATE TABLE "tbl_presupuesto_categorias" (
  "id_categoria" int NOT NULL AUTO_INCREMENT,
  "codigo" varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  "nombre" varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  "orden" int DEFAULT '0',
  "activo" tinyint(1) DEFAULT '1',
  "created_at" datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("id_categoria"),
  UNIQUE KEY "uk_codigo" ("codigo")
);

-- Tabla: tbl_presupuesto_detalle
DROP TABLE IF EXISTS `tbl_presupuesto_detalle`;
CREATE TABLE "tbl_presupuesto_detalle" (
  "id_detalle" int NOT NULL AUTO_INCREMENT,
  "id_item" int NOT NULL,
  "mes" int NOT NULL COMMENT '1-12 para meses del año',
  "anio" int NOT NULL,
  "presupuestado" decimal(15,2) DEFAULT '0.00',
  "ejecutado" decimal(15,2) DEFAULT '0.00',
  "notas" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "updated_at" datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id_detalle"),
  UNIQUE KEY "uk_item_mes_anio" ("id_item","mes","anio"),
  KEY "idx_item" ("id_item"),
  KEY "idx_mes_anio" ("mes","anio"),
  CONSTRAINT "tbl_presupuesto_detalle_ibfk_1" FOREIGN KEY ("id_item") REFERENCES "tbl_presupuesto_items" ("id_item") ON DELETE CASCADE
);

-- Tabla: tbl_presupuesto_items
DROP TABLE IF EXISTS `tbl_presupuesto_items`;
CREATE TABLE "tbl_presupuesto_items" (
  "id_item" int NOT NULL AUTO_INCREMENT,
  "id_presupuesto" int NOT NULL,
  "id_categoria" int NOT NULL,
  "codigo_item" varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ej: 1.1, 3.2, 4.1',
  "actividad" varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  "descripcion" text COLLATE utf8mb4_unicode_ci,
  "orden" int DEFAULT '0',
  "activo" tinyint(1) DEFAULT '1',
  "created_at" datetime DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id_item"),
  KEY "idx_presupuesto" ("id_presupuesto"),
  KEY "idx_categoria" ("id_categoria"),
  CONSTRAINT "tbl_presupuesto_items_ibfk_1" FOREIGN KEY ("id_presupuesto") REFERENCES "tbl_presupuesto_sst" ("id_presupuesto") ON DELETE CASCADE,
  CONSTRAINT "tbl_presupuesto_items_ibfk_2" FOREIGN KEY ("id_categoria") REFERENCES "tbl_presupuesto_categorias" ("id_categoria")
);

-- Tabla: tbl_presupuesto_sst
DROP TABLE IF EXISTS `tbl_presupuesto_sst`;
CREATE TABLE "tbl_presupuesto_sst" (
  "id_presupuesto" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "anio" int NOT NULL,
  "mes_inicio" int DEFAULT '1' COMMENT '1=Enero, 2=Febrero, etc.',
  "estado" enum('borrador','aprobado','cerrado') COLLATE utf8mb4_unicode_ci DEFAULT 'borrador',
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "created_at" datetime DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id_presupuesto"),
  UNIQUE KEY "uk_cliente_anio" ("id_cliente","anio"),
  KEY "idx_cliente" ("id_cliente"),
  KEY "idx_anio" ("anio")
);

-- Tabla: tbl_probabilidad_peligros
DROP TABLE IF EXISTS `tbl_probabilidad_peligros`;
CREATE TABLE "tbl_probabilidad_peligros" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_inspeccion" date NOT NULL,
  "sismos" enum('poco_probable','probable','muy_probable') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "inundaciones" enum('poco_probable','probable','muy_probable') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "vendavales" enum('poco_probable','probable','muy_probable') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "atentados" enum('poco_probable','probable','muy_probable') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "asalto_hurto" enum('poco_probable','probable','muy_probable') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "vandalismo" enum('poco_probable','probable','muy_probable') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "incendios" enum('poco_probable','probable','muy_probable') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "explosiones" enum('poco_probable','probable','muy_probable') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "inhalacion_gases" enum('poco_probable','probable','muy_probable') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "falla_estructural" enum('poco_probable','probable','muy_probable') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "intoxicacion_alimentos" enum('poco_probable','probable','muy_probable') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "densidad_poblacional" enum('poco_probable','probable','muy_probable') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_prob_pel_cliente" ("id_cliente"),
  KEY "idx_prob_pel_consultor" ("id_consultor"),
  KEY "idx_prob_pel_estado" ("estado"),
  CONSTRAINT "fk_prob_pel_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_programa_agua_potable
DROP TABLE IF EXISTS `tbl_programa_agua_potable`;
CREATE TABLE "tbl_programa_agua_potable" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_programa" date NOT NULL,
  "nombre_responsable" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "cantidad_tanques" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "capacidad_individual" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "capacidad_total" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_prog_agua_cliente" ("id_cliente"),
  KEY "idx_prog_agua_consultor" ("id_consultor"),
  KEY "idx_prog_agua_estado" ("estado"),
  CONSTRAINT "fk_prog_agua_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_programa_limpieza
DROP TABLE IF EXISTS `tbl_programa_limpieza`;
CREATE TABLE "tbl_programa_limpieza" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_programa" date NOT NULL,
  "nombre_responsable" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_prog_limp_cliente" ("id_cliente"),
  KEY "idx_prog_limp_consultor" ("id_consultor"),
  KEY "idx_prog_limp_estado" ("estado"),
  CONSTRAINT "fk_prog_limp_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_programa_plagas
DROP TABLE IF EXISTS `tbl_programa_plagas`;
CREATE TABLE "tbl_programa_plagas" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_programa" date NOT NULL,
  "nombre_responsable" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_prog_plag_cliente" ("id_cliente"),
  KEY "idx_prog_plag_consultor" ("id_consultor"),
  KEY "idx_prog_plag_estado" ("estado"),
  CONSTRAINT "fk_prog_plag_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_programa_residuos
DROP TABLE IF EXISTS `tbl_programa_residuos`;
CREATE TABLE "tbl_programa_residuos" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "fecha_programa" date NOT NULL,
  "nombre_responsable" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "flujo_residente" text COLLATE utf8mb4_unicode_ci COMMENT 'Flujo del residente para disposición de residuos',
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_prog_res_cliente" ("id_cliente"),
  KEY "idx_prog_res_consultor" ("id_consultor"),
  KEY "idx_prog_res_estado" ("estado"),
  CONSTRAINT "fk_prog_res_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_proveedor_servicio
DROP TABLE IF EXISTS `tbl_proveedor_servicio`;
CREATE TABLE "tbl_proveedor_servicio" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "tipo_servicio" varchar(50) NOT NULL,
  "tipo_servicio_otro" varchar(100) DEFAULT NULL,
  "estado" enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  "razon_social" varchar(255) NOT NULL,
  "nit" varchar(20) NOT NULL,
  "email_empresa" varchar(150) DEFAULT NULL,
  "telefono_empresa" varchar(20) DEFAULT NULL,
  "nombre_responsable_sst" varchar(150) DEFAULT NULL,
  "email_responsable_sst" varchar(150) DEFAULT NULL,
  "cargo_responsable_sst" varchar(100) DEFAULT NULL,
  "telefono_responsable_sst" varchar(20) DEFAULT NULL,
  "id_consultor" int DEFAULT NULL,
  "created_at" datetime NOT NULL,
  "updated_at" datetime DEFAULT NULL,
  PRIMARY KEY ("id"),
  KEY "idx_cliente" ("id_cliente"),
  KEY "idx_estado" ("estado")
);

-- Tabla: tbl_pta_cliente
DROP TABLE IF EXISTS `tbl_pta_cliente`;
CREATE TABLE "tbl_pta_cliente" (
  "id_ptacliente" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "tipo_servicio" varchar(255) DEFAULT NULL,
  "phva_plandetrabajo" varchar(255) NOT NULL,
  "numeral_plandetrabajo" varchar(255) NOT NULL,
  "actividad_plandetrabajo" text NOT NULL,
  "responsable_sugerido_plandetrabajo" varchar(255) DEFAULT NULL,
  "fecha_propuesta" date DEFAULT '2025-12-31',
  "fecha_cierre" date DEFAULT NULL,
  "responsable_definido_paralaactividad" varchar(255) DEFAULT NULL,
  "estado_actividad" enum('ABIERTA','CERRADA','GESTIONANDO','CERRADA SIN EJECUCIÓN','CERRADA POR FIN CONTRATO') NOT NULL DEFAULT 'ABIERTA',
  "porcentaje_avance" decimal(5,2) DEFAULT '0.00',
  "semana" int DEFAULT NULL,
  "observaciones" text,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id_ptacliente"),
  KEY "idx_id_cliente" ("id_cliente"),
  KEY "idx_estado_actividad" ("estado_actividad"),
  KEY "idx_phva_plandetrabajo" ("phva_plandetrabajo"),
  KEY "idx_responsable_sugerido" ("responsable_sugerido_plandetrabajo"),
  KEY "idx_fecha_propuesta" ("fecha_propuesta")
);

-- Tabla: tbl_pta_cliente_audit
DROP TABLE IF EXISTS `tbl_pta_cliente_audit`;
CREATE TABLE "tbl_pta_cliente_audit" (
  "id_audit" int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID único de auditoría',
  "id_ptacliente" int NOT NULL COMMENT 'ID del registro modificado en tbl_pta_cliente',
  "id_cliente" int DEFAULT NULL COMMENT 'ID del cliente al que pertenece el registro',
  "accion" enum('INSERT','UPDATE','DELETE','BULK_UPDATE') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo de acción realizada',
  "campo_modificado" varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombre del campo que fue modificado',
  "valor_anterior" text COLLATE utf8mb4_unicode_ci COMMENT 'Valor antes del cambio',
  "valor_nuevo" text COLLATE utf8mb4_unicode_ci COMMENT 'Valor después del cambio',
  "id_usuario" int NOT NULL COMMENT 'ID del usuario que realizó el cambio',
  "nombre_usuario" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombre del usuario para referencia rápida',
  "email_usuario" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email del usuario',
  "rol_usuario" varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Rol del usuario al momento del cambio',
  "ip_address" varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Dirección IP desde donde se realizó el cambio',
  "user_agent" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Navegador/dispositivo del usuario',
  "metodo" varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Método del controlador que realizó el cambio',
  "descripcion" text COLLATE utf8mb4_unicode_ci COMMENT 'Descripción legible del cambio realizado',
  "fecha_accion" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora del cambio',
  PRIMARY KEY ("id_audit"),
  KEY "idx_ptacliente" ("id_ptacliente"),
  KEY "idx_cliente" ("id_cliente"),
  KEY "idx_usuario" ("id_usuario"),
  KEY "idx_fecha" ("fecha_accion"),
  KEY "idx_accion" ("accion"),
  KEY "idx_campo" ("campo_modificado")
);

-- Tabla: tbl_pta_cliente_old
DROP TABLE IF EXISTS `tbl_pta_cliente_old`;
CREATE TABLE "tbl_pta_cliente_old" (
  "id_ptacliente" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "tipo_servicio" varchar(255) DEFAULT NULL,
  "phva_plandetrabajo" varchar(255) NOT NULL,
  "numeral_plandetrabajo" varchar(255) NOT NULL,
  "actividad_plandetrabajo" text NOT NULL,
  "responsable_sugerido_plandetrabajo" varchar(255) DEFAULT NULL,
  "fecha_propuesta" date DEFAULT NULL,
  "fecha_cierre" date DEFAULT NULL,
  "responsable_definido_paralaactividad" varchar(255) DEFAULT NULL,
  "estado_actividad" varchar(50) NOT NULL DEFAULT 'ABIERTA',
  "porcentaje_avance" decimal(5,2) DEFAULT '0.00',
  "semana" int DEFAULT NULL,
  "observaciones" text,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id_ptacliente"),
  KEY "idx_porcentaje_avance" ("porcentaje_avance"),
  KEY "idx_fecha_estado" ("fecha_propuesta","estado_actividad"),
  KEY "idx_fecha_propuesta" ("fecha_propuesta")
);

-- Tabla: tbl_pta_transiciones
DROP TABLE IF EXISTS `tbl_pta_transiciones`;
CREATE TABLE "tbl_pta_transiciones" (
  "id_transicion" int NOT NULL AUTO_INCREMENT,
  "id_ptacliente" int NOT NULL COMMENT 'FK a tbl_pta_cliente.id_ptacliente',
  "id_cliente" int NOT NULL COMMENT 'FK al cliente',
  "estado_anterior" varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ABIERTA' COMMENT 'Siempre ABIERTA en este contexto',
  "estado_nuevo" varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Estado al que pasó (GESTIONANDO, CERRADA, etc.)',
  "id_usuario" int NOT NULL COMMENT 'Quién hizo el cambio',
  "nombre_usuario" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombre del usuario al momento del cambio',
  "fecha_transicion" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Cuándo ocurrió',
  PRIMARY KEY ("id_transicion"),
  KEY "idx_id_ptacliente" ("id_ptacliente"),
  KEY "idx_id_cliente" ("id_cliente"),
  KEY "idx_estado_nuevo" ("estado_nuevo"),
  KEY "idx_fecha_transicion" ("fecha_transicion"),
  KEY "idx_id_usuario" ("id_usuario")
);

-- Tabla: tbl_reporte
DROP TABLE IF EXISTS `tbl_reporte`;
CREATE TABLE "tbl_reporte" (
  "id_reporte" int NOT NULL AUTO_INCREMENT,
  "titulo_reporte" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "id_detailreport" int NOT NULL,
  "enlace" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "estado" enum('ABIERTO','GESTIONANDO','CERRADO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "observaciones" longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "id_report_type" int NOT NULL,
  "report_url" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "tag" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY ("id_reporte"),
  UNIQUE KEY "idx_tag" ("tag"),
  KEY "id_cliente" ("id_cliente"),
  KEY "fk_detailreport" ("id_detailreport"),
  KEY "fk_report_type" ("id_report_type"),
  KEY "idx_created_at" ("created_at"),
  KEY "idx_titulo_reporte" ("titulo_reporte"),
  KEY "idx_estado" ("estado"),
  KEY "idx_id_cliente" ("id_cliente"),
  KEY "idx_id_detailreport" ("id_detailreport"),
  KEY "idx_id_report_type" ("id_report_type"),
  KEY "idx_cliente_fecha" ("id_cliente","created_at"),
  CONSTRAINT "fk_detailreport" FOREIGN KEY ("id_detailreport") REFERENCES "detail_report" ("id_detailreport") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "fk_report_type" FOREIGN KEY ("id_report_type") REFERENCES "report_type_table" ("id_report_type") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "tbl_reporte_ibfk_1" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente")
);

-- Tabla: tbl_reporte_capacitacion
DROP TABLE IF EXISTS `tbl_reporte_capacitacion`;
CREATE TABLE "tbl_reporte_capacitacion" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "id_consultor" int DEFAULT NULL,
  "id_cronograma_capacitacion" int DEFAULT NULL,
  "fecha_capacitacion" date NOT NULL,
  "nombre_capacitacion" text COLLATE utf8mb4_unicode_ci,
  "objetivo_capacitacion" text COLLATE utf8mb4_unicode_ci,
  "perfil_asistentes" text COLLATE utf8mb4_unicode_ci,
  "nombre_capacitador" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "horas_duracion" decimal(4,1) DEFAULT NULL,
  "numero_asistentes" smallint DEFAULT '0',
  "numero_programados" smallint DEFAULT '0',
  "numero_evaluados" smallint DEFAULT '0',
  "promedio_calificaciones" decimal(5,2) DEFAULT NULL,
  "foto_listado_asistencia" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_capacitacion" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_evaluacion" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_otros_1" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "foto_otros_2" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "observaciones" text COLLATE utf8mb4_unicode_ci,
  "ruta_pdf" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('borrador','completo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "mostrar_evaluacion_induccion" tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY ("id"),
  KEY "idx_rep_cap_cliente" ("id_cliente"),
  KEY "idx_rep_cap_consultor" ("id_consultor"),
  KEY "idx_rep_cap_estado" ("estado"),
  KEY "idx_rep_cap_cronograma" ("id_cronograma_capacitacion"),
  CONSTRAINT "fk_rep_cap_cliente" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: tbl_roles
DROP TABLE IF EXISTS `tbl_roles`;
CREATE TABLE "tbl_roles" (
  "id_rol" int NOT NULL AUTO_INCREMENT,
  "nombre_rol" varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  "descripcion" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "permisos" json DEFAULT NULL,
  "created_at" datetime DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id_rol"),
  UNIQUE KEY "nombre_rol" ("nombre_rol")
);

-- Tabla: tbl_seguimiento_clientes
DROP TABLE IF EXISTS `tbl_seguimiento_clientes`;
CREATE TABLE "tbl_seguimiento_clientes" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_cliente" int NOT NULL,
  "asunto" varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  "mensaje" text COLLATE utf8mb4_unicode_ci NOT NULL,
  "opciones_fechas" text COLLATE utf8mb4_unicode_ci COMMENT 'JSON array de opciones de fecha',
  "consultor" varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Edison Cuervo',
  "cargo_consultor" varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Consultor SST',
  "activo" tinyint(1) NOT NULL DEFAULT '1',
  "detenido" tinyint(1) NOT NULL DEFAULT '0',
  "motivo_detencion" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "created_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_activo" ("activo","detenido"),
  KEY "idx_cliente" ("id_cliente")
);

-- Tabla: tbl_seguimiento_historial
DROP TABLE IF EXISTS `tbl_seguimiento_historial`;
CREATE TABLE "tbl_seguimiento_historial" (
  "id" int NOT NULL AUTO_INCREMENT,
  "id_seguimiento" int NOT NULL,
  "id_cliente" int NOT NULL,
  "fecha_envio" datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "estado" enum('ENVIADO','ERROR','DETENIDO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ENVIADO',
  "detalle" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY ("id"),
  KEY "idx_seguimiento" ("id_seguimiento"),
  KEY "idx_cliente" ("id_cliente"),
  KEY "idx_fecha" ("fecha_envio")
);

-- Tabla: tbl_sesiones_usuario
DROP TABLE IF EXISTS `tbl_sesiones_usuario`;
CREATE TABLE "tbl_sesiones_usuario" (
  "id_sesion" int NOT NULL AUTO_INCREMENT,
  "id_usuario" int NOT NULL,
  "inicio_sesion" datetime NOT NULL,
  "fin_sesion" datetime DEFAULT NULL,
  "duracion_segundos" int DEFAULT NULL,
  "ip_address" varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "user_agent" varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "estado" enum('activa','cerrada','expirada') COLLATE utf8mb4_unicode_ci DEFAULT 'activa',
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("id_sesion"),
  KEY "idx_usuario" ("id_usuario"),
  KEY "idx_inicio" ("inicio_sesion"),
  KEY "idx_estado" ("estado"),
  CONSTRAINT "tbl_sesiones_usuario_ibfk_1" FOREIGN KEY ("id_usuario") REFERENCES "tbl_usuarios" ("id_usuario") ON DELETE CASCADE
);

-- Tabla: tbl_tests
DROP TABLE IF EXISTS `tbl_tests`;
CREATE TABLE "tbl_tests" (
  "id_test" int NOT NULL AUTO_INCREMENT,
  "nombre_test" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "timestamp" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("id_test")
);

-- Tabla: tbl_urls
DROP TABLE IF EXISTS `tbl_urls`;
CREATE TABLE "tbl_urls" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tipo" varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  "nombre" varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  "url" varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
  "created_at" datetime DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id")
);

-- Tabla: tbl_usuario_roles
DROP TABLE IF EXISTS `tbl_usuario_roles`;
CREATE TABLE "tbl_usuario_roles" (
  "id_usuario" int NOT NULL,
  "id_rol" int NOT NULL,
  "created_at" datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("id_usuario","id_rol"),
  KEY "id_rol" ("id_rol"),
  CONSTRAINT "tbl_usuario_roles_ibfk_1" FOREIGN KEY ("id_usuario") REFERENCES "tbl_usuarios" ("id_usuario") ON DELETE CASCADE,
  CONSTRAINT "tbl_usuario_roles_ibfk_2" FOREIGN KEY ("id_rol") REFERENCES "tbl_roles" ("id_rol") ON DELETE CASCADE
);

-- Tabla: tbl_usuarios
DROP TABLE IF EXISTS `tbl_usuarios`;
CREATE TABLE "tbl_usuarios" (
  "id_usuario" int NOT NULL AUTO_INCREMENT,
  "email" varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  "password" varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  "nombre_completo" varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  "tipo_usuario" enum('admin','consultant','client') COLLATE utf8mb4_unicode_ci NOT NULL,
  "id_entidad" int DEFAULT NULL COMMENT 'id_cliente o id_consultor según tipo_usuario',
  "estado" enum('activo','inactivo','pendiente','bloqueado') COLLATE utf8mb4_unicode_ci DEFAULT 'activo',
  "ultimo_login" datetime DEFAULT NULL,
  "intentos_fallidos" int DEFAULT '0',
  "fecha_bloqueo" datetime DEFAULT NULL,
  "token_recuperacion" varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "token_expira" datetime DEFAULT NULL,
  "created_at" datetime DEFAULT CURRENT_TIMESTAMP,
  "updated_at" datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id_usuario"),
  UNIQUE KEY "email" ("email"),
  KEY "idx_email" ("email"),
  KEY "idx_tipo_usuario" ("tipo_usuario"),
  KEY "idx_estado" ("estado")
);

-- Tabla: tbl_variable_denominator
DROP TABLE IF EXISTS `tbl_variable_denominator`;
CREATE TABLE "tbl_variable_denominator" (
  "id_denominator_variable" int NOT NULL AUTO_INCREMENT,
  "denominator_variable_text" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "denominator_variable_data" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "created_at" timestamp NULL DEFAULT NULL,
  "updated_at" timestamp NULL DEFAULT NULL,
  PRIMARY KEY ("id_denominator_variable"),
  UNIQUE KEY "id_denominator_variable" ("id_denominator_variable")
);

-- Tabla: tbl_variable_numerator
DROP TABLE IF EXISTS `tbl_variable_numerator`;
CREATE TABLE "tbl_variable_numerator" (
  "id_numerator_variable" int NOT NULL AUTO_INCREMENT,
  "numerator_variable_text" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "numerator_variable_data" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "created_at" timestamp NULL DEFAULT NULL,
  "updated_at" timestamp NULL DEFAULT NULL,
  PRIMARY KEY ("id_numerator_variable"),
  UNIQUE KEY "id_numerator_variable" ("id_numerator_variable")
);

-- Tabla: tbl_vencimientos_mantenimientos
DROP TABLE IF EXISTS `tbl_vencimientos_mantenimientos`;
CREATE TABLE "tbl_vencimientos_mantenimientos" (
  "id_vencimientos_mmttos" int NOT NULL AUTO_INCREMENT,
  "id_mantenimiento" int NOT NULL,
  "id_cliente" int NOT NULL,
  "id_consultor" int NOT NULL,
  "fecha_vencimiento" date DEFAULT NULL,
  "estado_actividad" enum('sin ejecutar','ejecutado','CERRADA POR FIN CONTRATO') NOT NULL DEFAULT 'sin ejecutar',
  "fecha_realizacion" date DEFAULT NULL,
  "observaciones" text,
  PRIMARY KEY ("id_vencimientos_mmttos"),
  KEY "id_mantenimiento" ("id_mantenimiento"),
  KEY "id_cliente" ("id_cliente"),
  KEY "id_consultor" ("id_consultor"),
  CONSTRAINT "tbl_vencimientos_mantenimientos_ibfk_1" FOREIGN KEY ("id_mantenimiento") REFERENCES "tbl_mantenimientos" ("id_mantenimiento") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "tbl_vencimientos_mantenimientos_ibfk_2" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "tbl_vencimientos_mantenimientos_ibfk_3" FOREIGN KEY ("id_consultor") REFERENCES "tbl_consultor" ("id_consultor") ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabla: tbl_vigias
DROP TABLE IF EXISTS `tbl_vigias`;
CREATE TABLE "tbl_vigias" (
  "id_vigia" int NOT NULL AUTO_INCREMENT,
  "nombre_vigia" varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "cedula_vigia" varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "periodo_texto" varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  "firma_vigia" text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  "observaciones" text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  "id_cliente" int NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("id_vigia"),
  KEY "id_cliente" ("id_cliente"),
  CONSTRAINT "tbl_vigias_ibfk_1" FOREIGN KEY ("id_cliente") REFERENCES "tbl_clientes" ("id_cliente") ON DELETE CASCADE
);

-- Vista: cronograma_capacitaciones_cliente
DROP VIEW IF EXISTS `cronograma_capacitaciones_cliente`;
CREATE VIEW "cronograma_capacitaciones_cliente" AS select "cc"."id_cronograma_capacitacion" AS "id_cronograma_capacitacion","cc"."id_cliente" AS "id_cliente","c"."nombre_cliente" AS "nombre_cliente","c"."correo_cliente" AS "correo_cliente","c"."id_consultor" AS "id_consultor","con"."nombre_consultor" AS "nombre_consultor","con"."correo_consultor" AS "correo_consultor","cc"."id_capacitacion" AS "id_capacitacion","cap"."capacitacion" AS "capacitacion","cap"."objetivo_capacitacion" AS "objetivo_capacitacion","cc"."fecha_programada" AS "fecha_programada","cc"."fecha_de_realizacion" AS "fecha_de_realizacion","cc"."estado" AS "estado","cc"."perfil_de_asistentes" AS "perfil_de_asistentes","cc"."nombre_del_capacitador" AS "nombre_del_capacitador","cc"."horas_de_duracion_de_la_capacitacion" AS "horas_de_duracion_de_la_capacitacion","cc"."indicador_de_realizacion_de_la_capacitacion" AS "indicador_de_realizacion_de_la_capacitacion","cc"."numero_de_asistentes_a_capacitacion" AS "numero_de_asistentes_a_capacitacion","cc"."numero_total_de_personas_programadas" AS "numero_total_de_personas_programadas","cc"."porcentaje_cobertura" AS "porcentaje_cobertura","cc"."numero_de_personas_evaluadas" AS "numero_de_personas_evaluadas","cc"."promedio_de_calificaciones" AS "promedio_de_calificaciones","cc"."observaciones" AS "observaciones" from ((("tbl_cronog_capacitacion" "cc" join "tbl_clientes" "c" on(("cc"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("c"."id_consultor" = "con"."id_consultor"))) join "capacitaciones_sst" "cap" on(("cc"."id_capacitacion" = "cap"."id_capacitacion")));

-- Vista: evaluacion_inicial_cliente
DROP VIEW IF EXISTS `evaluacion_inicial_cliente`;
CREATE VIEW "evaluacion_inicial_cliente" AS select "ev"."id_ev_ini" AS "id_ev_ini","ev"."id_cliente" AS "id_cliente","cl"."nombre_cliente" AS "nombre_cliente","cl"."correo_cliente" AS "correo_cliente","cl"."telefono_1_cliente" AS "telefono_1_cliente","cl"."direccion_cliente" AS "direccion_cliente","ev"."ciclo" AS "ciclo","ev"."estandar" AS "estandar","ev"."detalle_estandar" AS "detalle_estandar","ev"."estandares_minimos" AS "estandares_minimos","ev"."numeral" AS "numeral","ev"."numerales_del_cliente" AS "numerales_del_cliente","ev"."siete" AS "siete","ev"."veintiun" AS "veintiun","ev"."sesenta" AS "sesenta","ev"."item_del_estandar" AS "item_del_estandar","ev"."evaluacion_inicial" AS "evaluacion_inicial","ev"."valor" AS "valor","ev"."puntaje_cuantitativo" AS "puntaje_cuantitativo","ev"."item" AS "item","ev"."criterio" AS "criterio","ev"."modo_de_verificacion" AS "modo_de_verificacion","ev"."calificacion" AS "calificacion","ev"."nivel_de_evaluacion" AS "nivel_de_evaluacion","ev"."observaciones" AS "observaciones","ev"."created_at" AS "created_at","ev"."updated_at" AS "updated_at" from ("evaluacion_inicial_sst" "ev" join "tbl_clientes" "cl" on(("ev"."id_cliente" = "cl"."id_cliente")));

-- Vista: evaluacion_inicial_cliente_consultor
DROP VIEW IF EXISTS `evaluacion_inicial_cliente_consultor`;
CREATE VIEW "evaluacion_inicial_cliente_consultor" AS select "ev"."id_ev_ini" AS "id_ev_ini","ev"."id_cliente" AS "id_cliente","cl"."nombre_cliente" AS "nombre_cliente","cl"."correo_cliente" AS "correo_cliente","cl"."telefono_1_cliente" AS "telefono_1_cliente","cl"."direccion_cliente" AS "direccion_cliente","cl"."estandares" AS "estandares","ev"."ciclo" AS "ciclo","ev"."estandar" AS "estandar","ev"."detalle_estandar" AS "detalle_estandar","ev"."estandares_minimos" AS "estandares_minimos","ev"."numeral" AS "numeral","ev"."numerales_del_cliente" AS "numerales_del_cliente","ev"."siete" AS "siete","ev"."veintiun" AS "veintiun","ev"."sesenta" AS "sesenta","ev"."item_del_estandar" AS "item_del_estandar","ev"."evaluacion_inicial" AS "evaluacion_inicial","ev"."valor" AS "valor","ev"."puntaje_cuantitativo" AS "puntaje_cuantitativo","ev"."item" AS "item","ev"."criterio" AS "criterio","ev"."modo_de_verificacion" AS "modo_de_verificacion","ev"."calificacion" AS "calificacion","ev"."nivel_de_evaluacion" AS "nivel_de_evaluacion","ev"."observaciones" AS "observaciones","ev"."created_at" AS "created_at","ev"."updated_at" AS "updated_at","con"."nombre_consultor" AS "nombre_consultor","con"."correo_consultor" AS "correo_consultor","con"."telefono_consultor" AS "telefono_consultor","con"."cedula_consultor" AS "cedula_consultor","con"."numero_licencia" AS "numero_licencia","con"."foto_consultor" AS "foto_consultor","con"."firma_consultor" AS "firma_consultor" from (("evaluacion_inicial_sst" "ev" join "tbl_clientes" "cl" on(("ev"."id_cliente" = "cl"."id_cliente"))) left join "tbl_consultor" "con" on(("cl"."id_consultor" = "con"."id_consultor")));

-- Vista: mantenimientos_por_vencer
DROP VIEW IF EXISTS `mantenimientos_por_vencer`;
CREATE VIEW "mantenimientos_por_vencer" AS select "v"."id_vencimientos_mmttos" AS "id_vencimientos_mmttos","v"."id_mantenimiento" AS "id_mantenimiento","m"."detalle_mantenimiento" AS "detalle_mantenimiento","v"."id_cliente" AS "id_cliente","c"."nombre_cliente" AS "nombre_cliente","c"."correo_cliente" AS "correo_cliente","c"."telefono_1_cliente" AS "telefono_1_cliente","c"."telefono_2_cliente" AS "telefono_2_cliente","c"."direccion_cliente" AS "direccion_cliente","v"."id_consultor" AS "id_consultor","con"."nombre_consultor" AS "nombre_consultor","con"."correo_consultor" AS "correo_consultor","v"."fecha_vencimiento" AS "fecha_vencimiento",(to_days("v"."fecha_vencimiento") - to_days(curdate())) AS "dias_restantes","v"."estado_actividad" AS "estado_actividad","v"."observaciones" AS "observaciones" from ((("tbl_vencimientos_mantenimientos" "v" join "tbl_mantenimientos" "m" on(("v"."id_mantenimiento" = "m"."id_mantenimiento"))) join "tbl_clientes" "c" on(("v"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("v"."id_consultor" = "con"."id_consultor"))) where (("v"."estado_actividad" = 'sin ejecutar') and (("v"."fecha_vencimiento" < curdate()) or ("v"."fecha_vencimiento" between curdate() and (curdate() + interval 1 month))));

-- Vista: pendientes_abiertos_vencidos
DROP VIEW IF EXISTS `pendientes_abiertos_vencidos`;
CREATE VIEW "pendientes_abiertos_vencidos" AS select "p"."id_pendientes" AS "id_pendientes","p"."id_cliente" AS "id_cliente","c"."nombre_cliente" AS "nombre_cliente","c"."correo_cliente" AS "correo_cliente","c"."id_consultor" AS "id_consultor","con"."nombre_consultor" AS "nombre_consultor","con"."correo_consultor" AS "correo_consultor","p"."responsable" AS "responsable","p"."tarea_actividad" AS "tarea_actividad","p"."fecha_asignacion" AS "fecha_asignacion","p"."fecha_cierre" AS "fecha_cierre","p"."estado" AS "estado","p"."estado_avance" AS "estado_avance","p"."evidencia_para_cerrarla" AS "evidencia_para_cerrarla","p"."conteo_dias" AS "conteo_dias","p"."created_at" AS "created_at","p"."updated_at" AS "updated_at" from (("tbl_pendientes" "p" join "tbl_clientes" "c" on(("p"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("c"."id_consultor" = "con"."id_consultor"))) where (("p"."estado" = 'ABIERTA') and ("p"."fecha_cierre" < curdate()));

-- Vista: pendientes_del_cliente
DROP VIEW IF EXISTS `pendientes_del_cliente`;
CREATE VIEW "pendientes_del_cliente" AS select "p"."id_pendientes" AS "id_pendientes","p"."id_cliente" AS "id_cliente","c"."nombre_cliente" AS "nombre_cliente","c"."correo_cliente" AS "correo_cliente","c"."id_consultor" AS "id_consultor","con"."nombre_consultor" AS "nombre_consultor","con"."correo_consultor" AS "correo_consultor","p"."responsable" AS "responsable","p"."tarea_actividad" AS "tarea_actividad","p"."fecha_asignacion" AS "fecha_asignacion","p"."fecha_cierre" AS "fecha_cierre","p"."estado" AS "estado","p"."estado_avance" AS "estado_avance","p"."evidencia_para_cerrarla" AS "evidencia_para_cerrarla","p"."conteo_dias" AS "conteo_dias","p"."created_at" AS "created_at","p"."updated_at" AS "updated_at" from (("tbl_pendientes" "p" join "tbl_clientes" "c" on(("p"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("c"."id_consultor" = "con"."id_consultor")));

-- Vista: plan_de_trabajo_del_cliente
DROP VIEW IF EXISTS `plan_de_trabajo_del_cliente`;
CREATE VIEW "plan_de_trabajo_del_cliente" AS select "pta"."id_ptacliente" AS "id_ptacliente","pta"."id_cliente" AS "id_cliente","c"."nombre_cliente" AS "nombre_cliente","c"."correo_cliente" AS "correo_cliente","c"."id_consultor" AS "id_consultor","con"."nombre_consultor" AS "nombre_consultor","con"."correo_consultor" AS "correo_consultor","pta"."tipo_servicio" AS "tipo_servicio","pta"."phva_plandetrabajo" AS "phva_plandetrabajo","pta"."numeral_plandetrabajo" AS "numeral_plandetrabajo","pta"."actividad_plandetrabajo" AS "actividad_plandetrabajo","pta"."responsable_sugerido_plandetrabajo" AS "responsable_sugerido_plandetrabajo","pta"."fecha_propuesta" AS "fecha_propuesta","pta"."fecha_cierre" AS "fecha_cierre","pta"."responsable_definido_paralaactividad" AS "responsable_definido_paralaactividad","pta"."estado_actividad" AS "estado_actividad","pta"."porcentaje_avance" AS "porcentaje_avance","pta"."semana" AS "semana","pta"."observaciones" AS "observaciones","pta"."created_at" AS "created_at","pta"."updated_at" AS "updated_at" from (("tbl_pta_cliente" "pta" join "tbl_clientes" "c" on(("pta"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("c"."id_consultor" = "con"."id_consultor")));

-- Vista: resumen_estandares_cliente
DROP VIEW IF EXISTS `resumen_estandares_cliente`;
CREATE VIEW "resumen_estandares_cliente" AS select "cli"."id_cliente" AS "id_cliente","cli"."nombre_cliente" AS "nombre_cliente","cli"."estandares" AS "estandares","con"."nombre_consultor" AS "nombre_consultor","con"."correo_consultor" AS "correo_consultor",round(sum("ev"."valor"),2) AS "total_valor",round(sum("ev"."puntaje_cuantitativo"),2) AS "total_puntaje",round(((100 * sum("ev"."puntaje_cuantitativo")) / nullif(sum("ev"."valor"),0)),2) AS "porcentaje_cumplimiento" from (("evaluacion_inicial_sst" "ev" join "tbl_clientes" "cli" on(("ev"."id_cliente" = "cli"."id_cliente"))) left join "tbl_consultor" "con" on(("cli"."id_consultor" = "con"."id_consultor"))) where ("cli"."estado" = 'activo') group by "cli"."id_cliente";

-- Vista: resumen_mensual_plan_trabajo
DROP VIEW IF EXISTS `resumen_mensual_plan_trabajo`;
CREATE VIEW "resumen_mensual_plan_trabajo" AS select "pta"."id_cliente" AS "id_cliente","cli"."nombre_cliente" AS "nombre_cliente","cli"."estandares" AS "estandares","con"."nombre_consultor" AS "nombre_consultor","con"."correo_consultor" AS "correo_consultor",count(0) AS "total_actividades",sum((case when ("pta"."estado_actividad" = 'ABIERTA') then 1 else 0 end)) AS "actividades_abiertas",round(((100 * sum((case when ("pta"."estado_actividad" = 'ABIERTA') then 1 else 0 end))) / count(0)),2) AS "porcentaje_abiertas" from (("plan_de_trabajo_del_cliente" "pta" join "tbl_clientes" "cli" on(("pta"."id_cliente" = "cli"."id_cliente"))) left join "tbl_consultor" "con" on(("cli"."id_consultor" = "con"."id_consultor"))) where ("cli"."estado" = 'activo') group by "pta"."id_cliente";

-- Vista: v_evaluacion_inicial_sst
DROP VIEW IF EXISTS `v_evaluacion_inicial_sst`;
CREATE VIEW "v_evaluacion_inicial_sst" AS select "e"."id_ev_ini" AS "id_ev_ini","e"."created_at" AS "created_at","e"."updated_at" AS "updated_at","e"."id_cliente" AS "id_cliente","e"."ciclo" AS "ciclo","e"."estandar" AS "estandar","e"."detalle_estandar" AS "detalle_estandar","e"."estandares_minimos" AS "estandares_minimos","e"."numeral" AS "numeral","e"."numerales_del_cliente" AS "numerales_del_cliente","e"."siete" AS "siete","e"."veintiun" AS "veintiun","e"."sesenta" AS "sesenta","e"."item_del_estandar" AS "item_del_estandar","e"."evaluacion_inicial" AS "evaluacion_inicial","e"."valor" AS "valor","e"."puntaje_cuantitativo" AS "puntaje_cuantitativo","e"."item" AS "item","e"."criterio" AS "criterio","e"."modo_de_verificacion" AS "modo_de_verificacion","e"."calificacion" AS "calificacion","e"."nivel_de_evaluacion" AS "nivel_de_evaluacion","e"."observaciones" AS "observaciones","c"."nombre_cliente" AS "nombre_cliente" from ("evaluacion_inicial_sst" "e" join "tbl_clientes" "c" on(("e"."id_cliente" = "c"."id_cliente")));

-- Vista: v_historial_resumen_estandares
DROP VIEW IF EXISTS `v_historial_resumen_estandares`;
CREATE VIEW "v_historial_resumen_estandares" AS select "h"."id_cliente" AS "id_cliente","h"."nombre_cliente" AS "nombre_cliente","h"."estandares" AS "estandares","h"."nombre_consultor" AS "nombre_consultor","h"."correo_consultor" AS "correo_consultor","h"."total_valor" AS "total_valor","h"."total_puntaje" AS "total_puntaje","h"."porcentaje_cumplimiento" AS "porcentaje_cumplimiento","h"."fecha_extraccion" AS "fecha_extraccion","c"."nombre_cliente" AS "nombre_cliente_fk" from ("historial_resumen_estandares" "h" join "tbl_clientes" "c" on(("h"."id_cliente" = "c"."id_cliente")));

-- Vista: v_historial_resumen_plan_trabajo
DROP VIEW IF EXISTS `v_historial_resumen_plan_trabajo`;
CREATE VIEW "v_historial_resumen_plan_trabajo" AS select "h"."id_cliente" AS "id_cliente","h"."nombre_cliente" AS "nombre_cliente","h"."estandares" AS "estandares","h"."nombre_consultor" AS "nombre_consultor","h"."correo_consultor" AS "correo_consultor","h"."total_actividades" AS "total_actividades","h"."actividades_abiertas" AS "actividades_abiertas","h"."porcentaje_abiertas" AS "porcentaje_abiertas","h"."fecha_extraccion" AS "fecha_extraccion","c"."nombre_cliente" AS "nombre_cliente_fk" from ("historial_resumen_plan_trabajo" "h" join "tbl_clientes" "c" on(("h"."id_cliente" = "c"."id_cliente")));

-- Vista: v_tbl_acta_visita
DROP VIEW IF EXISTS `v_tbl_acta_visita`;
CREATE VIEW "v_tbl_acta_visita" AS select "a"."id" AS "id","a"."id_cliente" AS "id_cliente","a"."id_consultor" AS "id_consultor","a"."fecha_visita" AS "fecha_visita","a"."hora_visita" AS "hora_visita","a"."ubicacion_gps" AS "ubicacion_gps","a"."motivo" AS "motivo","a"."modalidad" AS "modalidad","a"."cartera" AS "cartera","a"."observaciones" AS "observaciones","a"."proxima_reunion_fecha" AS "proxima_reunion_fecha","a"."proxima_reunion_hora" AS "proxima_reunion_hora","a"."firma_administrador" AS "firma_administrador","a"."firma_vigia" AS "firma_vigia","a"."firma_consultor" AS "firma_consultor","a"."soporte_lavado_tanques" AS "soporte_lavado_tanques","a"."soporte_plagas" AS "soporte_plagas","a"."ruta_pdf" AS "ruta_pdf","a"."estado" AS "estado","a"."agenda_id" AS "agenda_id","a"."created_at" AS "created_at","a"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_acta_visita" "a" join "tbl_clientes" "c" on(("a"."id_cliente" = "c"."id_cliente"))) join "tbl_consultor" "con" on(("a"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_acta_visita_fotos
DROP VIEW IF EXISTS `v_tbl_acta_visita_fotos`;
CREATE VIEW "v_tbl_acta_visita_fotos" AS select "f"."id" AS "id","f"."id_acta_visita" AS "id_acta_visita","f"."ruta_archivo" AS "ruta_archivo","f"."tipo" AS "tipo","f"."descripcion" AS "descripcion","f"."created_at" AS "created_at","av"."fecha_visita" AS "fecha_visita","c"."nombre_cliente" AS "nombre_cliente" from (("tbl_acta_visita_fotos" "f" join "tbl_acta_visita" "av" on(("f"."id_acta_visita" = "av"."id"))) join "tbl_clientes" "c" on(("av"."id_cliente" = "c"."id_cliente")));

-- Vista: v_tbl_acta_visita_integrantes
DROP VIEW IF EXISTS `v_tbl_acta_visita_integrantes`;
CREATE VIEW "v_tbl_acta_visita_integrantes" AS select "i"."id" AS "id","i"."id_acta_visita" AS "id_acta_visita","i"."nombre" AS "nombre","i"."rol" AS "rol","i"."orden" AS "orden","av"."fecha_visita" AS "fecha_visita","c"."nombre_cliente" AS "nombre_cliente" from (("tbl_acta_visita_integrantes" "i" join "tbl_acta_visita" "av" on(("i"."id_acta_visita" = "av"."id"))) join "tbl_clientes" "c" on(("av"."id_cliente" = "c"."id_cliente")));

-- Vista: v_tbl_acta_visita_pta
DROP VIEW IF EXISTS `v_tbl_acta_visita_pta`;
CREATE VIEW "v_tbl_acta_visita_pta" AS select "p"."id" AS "id","p"."id_acta_visita" AS "id_acta_visita","p"."id_ptacliente" AS "id_ptacliente","p"."cerrada" AS "cerrada","p"."justificacion_no_cierre" AS "justificacion_no_cierre","p"."created_at" AS "created_at","av"."fecha_visita" AS "fecha_visita","c"."nombre_cliente" AS "nombre_cliente","pta"."actividad_plandetrabajo" AS "actividad_plandetrabajo","pta"."estado_actividad" AS "estado_actividad" from ((("tbl_acta_visita_pta" "p" join "tbl_acta_visita" "av" on(("p"."id_acta_visita" = "av"."id"))) join "tbl_clientes" "c" on(("av"."id_cliente" = "c"."id_cliente"))) join "tbl_pta_cliente" "pta" on(("p"."id_ptacliente" = "pta"."id_ptacliente")));

-- Vista: v_tbl_acta_visita_temas
DROP VIEW IF EXISTS `v_tbl_acta_visita_temas`;
CREATE VIEW "v_tbl_acta_visita_temas" AS select "t"."id" AS "id","t"."id_acta_visita" AS "id_acta_visita","t"."descripcion" AS "descripcion","t"."orden" AS "orden","av"."fecha_visita" AS "fecha_visita","c"."nombre_cliente" AS "nombre_cliente" from (("tbl_acta_visita_temas" "t" join "tbl_acta_visita" "av" on(("t"."id_acta_visita" = "av"."id"))) join "tbl_clientes" "c" on(("av"."id_cliente" = "c"."id_cliente")));

-- Vista: v_tbl_agendamientos
DROP VIEW IF EXISTS `v_tbl_agendamientos`;
CREATE VIEW "v_tbl_agendamientos" AS select "a"."id" AS "id","a"."id_cliente" AS "id_cliente","a"."id_consultor" AS "id_consultor","a"."fecha_visita" AS "fecha_visita","a"."hora_visita" AS "hora_visita","a"."frecuencia" AS "frecuencia","a"."estado" AS "estado","a"."confirmacion_calendar" AS "confirmacion_calendar","a"."preparacion_cliente" AS "preparacion_cliente","a"."observaciones" AS "observaciones","a"."email_enviado" AS "email_enviado","a"."fecha_email_enviado" AS "fecha_email_enviado","a"."created_at" AS "created_at","a"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_agendamientos" "a" left join "tbl_clientes" "c" on(("a"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("a"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_asistencia_induccion
DROP VIEW IF EXISTS `v_tbl_asistencia_induccion`;
CREATE VIEW "v_tbl_asistencia_induccion" AS select "a"."id" AS "id","a"."id_cliente" AS "id_cliente","a"."id_consultor" AS "id_consultor","a"."fecha_sesion" AS "fecha_sesion","a"."tema" AS "tema","a"."lugar" AS "lugar","a"."objetivo" AS "objetivo","a"."capacitador" AS "capacitador","a"."tipo_charla" AS "tipo_charla","a"."material" AS "material","a"."tiempo_horas" AS "tiempo_horas","a"."observaciones" AS "observaciones","a"."ruta_pdf_asistencia" AS "ruta_pdf_asistencia","a"."ruta_pdf_responsabilidades" AS "ruta_pdf_responsabilidades","a"."estado" AS "estado","a"."created_at" AS "created_at","a"."updated_at" AS "updated_at","a"."evaluacion_habilitada" AS "evaluacion_habilitada","a"."evaluacion_token" AS "evaluacion_token","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_asistencia_induccion" "a" join "tbl_clientes" "c" on(("a"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("a"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_asistencia_induccion_asistente
DROP VIEW IF EXISTS `v_tbl_asistencia_induccion_asistente`;
CREATE VIEW "v_tbl_asistencia_induccion_asistente" AS select "a"."id" AS "id","a"."id_asistencia" AS "id_asistencia","a"."nombre" AS "nombre","a"."cedula" AS "cedula","a"."cargo" AS "cargo","a"."firma" AS "firma","a"."created_at" AS "created_at","a"."updated_at" AS "updated_at","asi"."fecha_sesion" AS "fecha_sesion","asi"."tema" AS "tema","c"."nombre_cliente" AS "nombre_cliente" from (("tbl_asistencia_induccion_asistente" "a" join "tbl_asistencia_induccion" "asi" on(("a"."id_asistencia" = "asi"."id"))) join "tbl_clientes" "c" on(("asi"."id_cliente" = "c"."id_cliente")));

-- Vista: v_tbl_auditoria_zona_residuos
DROP VIEW IF EXISTS `v_tbl_auditoria_zona_residuos`;
CREATE VIEW "v_tbl_auditoria_zona_residuos" AS select "a"."id" AS "id","a"."id_cliente" AS "id_cliente","a"."id_consultor" AS "id_consultor","a"."fecha_inspeccion" AS "fecha_inspeccion","a"."estado_acceso" AS "estado_acceso","a"."foto_acceso" AS "foto_acceso","a"."estado_techo_pared_pisos" AS "estado_techo_pared_pisos","a"."foto_techo_pared_pisos" AS "foto_techo_pared_pisos","a"."estado_ventilacion" AS "estado_ventilacion","a"."foto_ventilacion" AS "foto_ventilacion","a"."estado_prevencion_incendios" AS "estado_prevencion_incendios","a"."foto_prevencion_incendios" AS "foto_prevencion_incendios","a"."estado_drenajes" AS "estado_drenajes","a"."foto_drenajes" AS "foto_drenajes","a"."proliferacion_plagas" AS "proliferacion_plagas","a"."foto_proliferacion_plagas" AS "foto_proliferacion_plagas","a"."estado_recipientes" AS "estado_recipientes","a"."foto_recipientes" AS "foto_recipientes","a"."estado_reciclaje" AS "estado_reciclaje","a"."foto_reciclaje" AS "foto_reciclaje","a"."estado_iluminarias" AS "estado_iluminarias","a"."foto_iluminarias" AS "foto_iluminarias","a"."estado_senalizacion" AS "estado_senalizacion","a"."foto_senalizacion" AS "foto_senalizacion","a"."estado_limpieza_desinfeccion" AS "estado_limpieza_desinfeccion","a"."foto_limpieza_desinfeccion" AS "foto_limpieza_desinfeccion","a"."estado_poseta" AS "estado_poseta","a"."foto_poseta" AS "foto_poseta","a"."observaciones" AS "observaciones","a"."ruta_pdf" AS "ruta_pdf","a"."estado" AS "estado","a"."created_at" AS "created_at","a"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_auditoria_zona_residuos" "a" join "tbl_clientes" "c" on(("a"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("a"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_carta_vigia
DROP VIEW IF EXISTS `v_tbl_carta_vigia`;
CREATE VIEW "v_tbl_carta_vigia" AS select "cv"."id" AS "id","cv"."id_cliente" AS "id_cliente","cv"."id_consultor" AS "id_consultor","cv"."nombre_vigia" AS "nombre_vigia","cv"."documento_vigia" AS "documento_vigia","cv"."email_vigia" AS "email_vigia","cv"."telefono_vigia" AS "telefono_vigia","cv"."token_firma" AS "token_firma","cv"."token_firma_expiracion" AS "token_firma_expiracion","cv"."estado_firma" AS "estado_firma","cv"."firma_imagen" AS "firma_imagen","cv"."firma_ip" AS "firma_ip","cv"."firma_fecha" AS "firma_fecha","cv"."codigo_verificacion" AS "codigo_verificacion","cv"."ruta_pdf" AS "ruta_pdf","cv"."created_at" AS "created_at","cv"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_carta_vigia" "cv" join "tbl_clientes" "c" on(("cv"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("cv"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_chat_log
DROP VIEW IF EXISTS `v_tbl_chat_log`;
CREATE VIEW "v_tbl_chat_log" AS select "cl"."id" AS "id","cl"."id_usuario" AS "id_usuario","cl"."rol" AS "rol","cl"."tipo_operacion" AS "tipo_operacion","cl"."detalle" AS "detalle","cl"."ip_address" AS "ip_address","cl"."created_at" AS "created_at","u"."nombre_completo" AS "nombre_completo","u"."email" AS "email","u"."tipo_usuario" AS "tipo_usuario" from ("tbl_chat_log" "cl" join "tbl_usuarios" "u" on(("cl"."id_usuario" = "u"."id_usuario")));

-- Vista: v_tbl_ciclos_visita
DROP VIEW IF EXISTS `v_tbl_ciclos_visita`;
CREATE VIEW "v_tbl_ciclos_visita" AS select "cv"."id" AS "id","cv"."id_cliente" AS "id_cliente","cv"."id_consultor" AS "id_consultor","cv"."anio" AS "anio","cv"."mes_esperado" AS "mes_esperado","cv"."estandar" AS "estandar","cv"."fecha_agendada" AS "fecha_agendada","cv"."id_agendamiento" AS "id_agendamiento","cv"."fecha_acta" AS "fecha_acta","cv"."id_acta" AS "id_acta","cv"."estatus_agenda" AS "estatus_agenda","cv"."estatus_mes" AS "estatus_mes","cv"."alerta_enviada" AS "alerta_enviada","cv"."confirmacion_enviada" AS "confirmacion_enviada","cv"."observaciones" AS "observaciones","cv"."created_at" AS "created_at","cv"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor","av"."fecha_visita" AS "fecha_visita" from ((("tbl_ciclos_visita" "cv" join "tbl_clientes" "c" on(("cv"."id_cliente" = "c"."id_cliente"))) join "tbl_consultor" "con" on(("cv"."id_consultor" = "con"."id_consultor"))) left join "tbl_acta_visita" "av" on(("cv"."id_acta" = "av"."id")));

-- Vista: v_tbl_contratos
DROP VIEW IF EXISTS `v_tbl_contratos`;
CREATE VIEW "v_tbl_contratos" AS select "ct"."id_contrato" AS "id_contrato","ct"."id_cliente" AS "id_cliente","ct"."numero_contrato" AS "numero_contrato","ct"."fecha_inicio" AS "fecha_inicio","ct"."fecha_fin" AS "fecha_fin","ct"."valor_contrato" AS "valor_contrato","ct"."tipo_contrato" AS "tipo_contrato","ct"."estado" AS "estado","ct"."observaciones" AS "observaciones","ct"."clausula_cuarta_duracion" AS "clausula_cuarta_duracion","ct"."nombre_rep_legal_cliente" AS "nombre_rep_legal_cliente","ct"."cedula_rep_legal_cliente" AS "cedula_rep_legal_cliente","ct"."direccion_cliente" AS "direccion_cliente","ct"."telefono_cliente" AS "telefono_cliente","ct"."email_cliente" AS "email_cliente","ct"."nombre_rep_legal_contratista" AS "nombre_rep_legal_contratista","ct"."cedula_rep_legal_contratista" AS "cedula_rep_legal_contratista","ct"."email_contratista" AS "email_contratista","ct"."nombre_responsable_sgsst" AS "nombre_responsable_sgsst","ct"."cedula_responsable_sgsst" AS "cedula_responsable_sgsst","ct"."licencia_responsable_sgsst" AS "licencia_responsable_sgsst","ct"."email_responsable_sgsst" AS "email_responsable_sgsst","ct"."id_consultor_responsable" AS "id_consultor_responsable","ct"."valor_mensual" AS "valor_mensual","ct"."numero_cuotas" AS "numero_cuotas","ct"."frecuencia_visitas" AS "frecuencia_visitas","ct"."cuenta_bancaria" AS "cuenta_bancaria","ct"."banco" AS "banco","ct"."tipo_cuenta" AS "tipo_cuenta","ct"."contrato_generado" AS "contrato_generado","ct"."fecha_generacion_contrato" AS "fecha_generacion_contrato","ct"."ruta_pdf_contrato" AS "ruta_pdf_contrato","ct"."contrato_enviado" AS "contrato_enviado","ct"."fecha_envio_contrato" AS "fecha_envio_contrato","ct"."email_envio_contrato" AS "email_envio_contrato","ct"."created_at" AS "created_at","ct"."updated_at" AS "updated_at","ct"."created_by" AS "created_by","ct"."token_firma" AS "token_firma","ct"."token_firma_expiracion" AS "token_firma_expiracion","ct"."estado_firma" AS "estado_firma","ct"."firma_cliente_nombre" AS "firma_cliente_nombre","ct"."firma_cliente_cedula" AS "firma_cliente_cedula","ct"."firma_cliente_imagen" AS "firma_cliente_imagen","ct"."firma_cliente_ip" AS "firma_cliente_ip","ct"."firma_cliente_fecha" AS "firma_cliente_fecha","ct"."clausula_primera_objeto" AS "clausula_primera_objeto","ct"."codigo_verificacion" AS "codigo_verificacion","c"."nombre_cliente" AS "nombre_cliente" from ("tbl_contratos" "ct" join "tbl_clientes" "c" on(("ct"."id_cliente" = "c"."id_cliente")));

-- Vista: v_tbl_cronog_capacitacion
DROP VIEW IF EXISTS `v_tbl_cronog_capacitacion`;
CREATE VIEW "v_tbl_cronog_capacitacion" AS select "cc"."id_cronograma_capacitacion" AS "id_cronograma_capacitacion","cc"."id_capacitacion" AS "id_capacitacion","cc"."nombre_capacitacion" AS "nombre_capacitacion","cc"."objetivo_capacitacion" AS "objetivo_capacitacion","cc"."id_cliente" AS "id_cliente","cc"."fecha_programada" AS "fecha_programada","cc"."fecha_de_realizacion" AS "fecha_de_realizacion","cc"."estado" AS "estado","cc"."perfil_de_asistentes" AS "perfil_de_asistentes","cc"."nombre_del_capacitador" AS "nombre_del_capacitador","cc"."horas_de_duracion_de_la_capacitacion" AS "horas_de_duracion_de_la_capacitacion","cc"."indicador_de_realizacion_de_la_capacitacion" AS "indicador_de_realizacion_de_la_capacitacion","cc"."numero_de_asistentes_a_capacitacion" AS "numero_de_asistentes_a_capacitacion","cc"."numero_total_de_personas_programadas" AS "numero_total_de_personas_programadas","cc"."porcentaje_cobertura" AS "porcentaje_cobertura","cc"."numero_de_personas_evaluadas" AS "numero_de_personas_evaluadas","cc"."promedio_de_calificaciones" AS "promedio_de_calificaciones","cc"."observaciones" AS "observaciones","cc"."created_at" AS "created_at","cc"."updated_at" AS "updated_at","cc"."id_reporte_capacitacion" AS "id_reporte_capacitacion","c"."nombre_cliente" AS "nombre_cliente","cs"."capacitacion" AS "nombre_capacitacion_catalogo" from (("tbl_cronog_capacitacion" "cc" join "tbl_clientes" "c" on(("cc"."id_cliente" = "c"."id_cliente"))) left join "capacitaciones_sst" "cs" on(("cc"."id_capacitacion" = "cs"."id_capacitacion")));

-- Vista: v_tbl_cronog_capacitacion_old
DROP VIEW IF EXISTS `v_tbl_cronog_capacitacion_old`;
CREATE VIEW "v_tbl_cronog_capacitacion_old" AS select "cc"."id_cronograma_capacitacion" AS "id_cronograma_capacitacion","cc"."id_capacitacion" AS "id_capacitacion","cc"."id_cliente" AS "id_cliente","cc"."fecha_programada" AS "fecha_programada","cc"."fecha_de_realizacion" AS "fecha_de_realizacion","cc"."estado" AS "estado","cc"."perfil_de_asistentes" AS "perfil_de_asistentes","cc"."nombre_del_capacitador" AS "nombre_del_capacitador","cc"."horas_de_duracion_de_la_capacitacion" AS "horas_de_duracion_de_la_capacitacion","cc"."indicador_de_realizacion_de_la_capacitacion" AS "indicador_de_realizacion_de_la_capacitacion","cc"."numero_de_asistentes_a_capacitacion" AS "numero_de_asistentes_a_capacitacion","cc"."numero_total_de_personas_programadas" AS "numero_total_de_personas_programadas","cc"."porcentaje_cobertura" AS "porcentaje_cobertura","cc"."numero_de_personas_evaluadas" AS "numero_de_personas_evaluadas","cc"."promedio_de_calificaciones" AS "promedio_de_calificaciones","cc"."observaciones" AS "observaciones","c"."nombre_cliente" AS "nombre_cliente","cs"."capacitacion" AS "nombre_capacitacion" from (("tbl_cronog_capacitacion_old" "cc" join "tbl_clientes" "c" on(("cc"."id_cliente" = "c"."id_cliente"))) join "capacitaciones_sst" "cs" on(("cc"."id_capacitacion" = "cs"."id_capacitacion")));

-- Vista: v_tbl_dotacion_aseadora
DROP VIEW IF EXISTS `v_tbl_dotacion_aseadora`;
CREATE VIEW "v_tbl_dotacion_aseadora" AS select "d"."id" AS "id","d"."id_cliente" AS "id_cliente","d"."id_consultor" AS "id_consultor","d"."fecha_inspeccion" AS "fecha_inspeccion","d"."contratista" AS "contratista","d"."servicio" AS "servicio","d"."nombre_cargo" AS "nombre_cargo","d"."actividades_frecuentes" AS "actividades_frecuentes","d"."foto_cuerpo_completo" AS "foto_cuerpo_completo","d"."foto_cuarto_almacenamiento" AS "foto_cuarto_almacenamiento","d"."estado_tapabocas" AS "estado_tapabocas","d"."estado_guantes_nitrilo" AS "estado_guantes_nitrilo","d"."estado_guantes_caucho" AS "estado_guantes_caucho","d"."estado_gafas" AS "estado_gafas","d"."estado_uniforme" AS "estado_uniforme","d"."estado_sombrero" AS "estado_sombrero","d"."estado_zapato" AS "estado_zapato","d"."estado_botas_caucho" AS "estado_botas_caucho","d"."concepto_final" AS "concepto_final","d"."observaciones" AS "observaciones","d"."ruta_pdf" AS "ruta_pdf","d"."estado" AS "estado","d"."created_at" AS "created_at","d"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_dotacion_aseadora" "d" join "tbl_clientes" "c" on(("d"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("d"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_dotacion_todero
DROP VIEW IF EXISTS `v_tbl_dotacion_todero`;
CREATE VIEW "v_tbl_dotacion_todero" AS select "d"."id" AS "id","d"."id_cliente" AS "id_cliente","d"."id_consultor" AS "id_consultor","d"."fecha_inspeccion" AS "fecha_inspeccion","d"."contratista" AS "contratista","d"."servicio" AS "servicio","d"."nombre_cargo" AS "nombre_cargo","d"."actividades_frecuentes" AS "actividades_frecuentes","d"."foto_cuerpo_completo" AS "foto_cuerpo_completo","d"."foto_cuarto_almacenamiento" AS "foto_cuarto_almacenamiento","d"."estado_tapabocas" AS "estado_tapabocas","d"."estado_guantes_nitrilo" AS "estado_guantes_nitrilo","d"."estado_mascarilla_polvo" AS "estado_mascarilla_polvo","d"."estado_guantes_nylon" AS "estado_guantes_nylon","d"."estado_guantes_caucho" AS "estado_guantes_caucho","d"."estado_gafas" AS "estado_gafas","d"."estado_uniforme" AS "estado_uniforme","d"."estado_sombrero" AS "estado_sombrero","d"."estado_zapato" AS "estado_zapato","d"."estado_casco" AS "estado_casco","d"."estado_careta" AS "estado_careta","d"."estado_protector_auditivo" AS "estado_protector_auditivo","d"."estado_respirador" AS "estado_respirador","d"."estado_guantes_vaqueta" AS "estado_guantes_vaqueta","d"."estado_botas_dielectricas" AS "estado_botas_dielectricas","d"."estado_delantal_pvc" AS "estado_delantal_pvc","d"."concepto_final" AS "concepto_final","d"."observaciones" AS "observaciones","d"."ruta_pdf" AS "ruta_pdf","d"."estado" AS "estado","d"."created_at" AS "created_at","d"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_dotacion_todero" "d" join "tbl_clientes" "c" on(("d"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("d"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_dotacion_vigilante
DROP VIEW IF EXISTS `v_tbl_dotacion_vigilante`;
CREATE VIEW "v_tbl_dotacion_vigilante" AS select "d"."id" AS "id","d"."id_cliente" AS "id_cliente","d"."id_consultor" AS "id_consultor","d"."fecha_inspeccion" AS "fecha_inspeccion","d"."contratista" AS "contratista","d"."servicio" AS "servicio","d"."nombre_cargo" AS "nombre_cargo","d"."actividades_frecuentes" AS "actividades_frecuentes","d"."foto_cuerpo_completo" AS "foto_cuerpo_completo","d"."foto_cuarto_almacenamiento" AS "foto_cuarto_almacenamiento","d"."estado_uniforme" AS "estado_uniforme","d"."estado_chaqueta" AS "estado_chaqueta","d"."estado_radio" AS "estado_radio","d"."estado_baston" AS "estado_baston","d"."estado_calzado" AS "estado_calzado","d"."estado_gorra" AS "estado_gorra","d"."estado_carne" AS "estado_carne","d"."concepto_final" AS "concepto_final","d"."observaciones" AS "observaciones","d"."ruta_pdf" AS "ruta_pdf","d"."estado" AS "estado","d"."created_at" AS "created_at","d"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_dotacion_vigilante" "d" join "tbl_clientes" "c" on(("d"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("d"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_elemento_botiquin
DROP VIEW IF EXISTS `v_tbl_elemento_botiquin`;
CREATE VIEW "v_tbl_elemento_botiquin" AS select "e"."id" AS "id","e"."id_inspeccion" AS "id_inspeccion","e"."clave" AS "clave","e"."cantidad" AS "cantidad","e"."estado" AS "estado","e"."fecha_vencimiento" AS "fecha_vencimiento","e"."orden" AS "orden","e"."created_at" AS "created_at","ib"."fecha_inspeccion" AS "fecha_inspeccion","c"."nombre_cliente" AS "nombre_cliente" from (("tbl_elemento_botiquin" "e" join "tbl_inspeccion_botiquin" "ib" on(("e"."id_inspeccion" = "ib"."id"))) join "tbl_clientes" "c" on(("ib"."id_cliente" = "c"."id_cliente")));

-- Vista: v_tbl_evaluacion_induccion
DROP VIEW IF EXISTS `v_tbl_evaluacion_induccion`;
CREATE VIEW "v_tbl_evaluacion_induccion" AS select "e"."id" AS "id","e"."id_asistencia_induccion" AS "id_asistencia_induccion","e"."id_cliente" AS "id_cliente","e"."titulo" AS "titulo","e"."token" AS "token","e"."estado" AS "estado","e"."created_at" AS "created_at","e"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","asi"."fecha_sesion" AS "fecha_sesion","asi"."tema" AS "tema" from (("tbl_evaluacion_induccion" "e" join "tbl_clientes" "c" on(("e"."id_cliente" = "c"."id_cliente"))) left join "tbl_asistencia_induccion" "asi" on(("e"."id_asistencia_induccion" = "asi"."id")));

-- Vista: v_tbl_evaluacion_induccion_respuesta
DROP VIEW IF EXISTS `v_tbl_evaluacion_induccion_respuesta`;
CREATE VIEW "v_tbl_evaluacion_induccion_respuesta" AS select "r"."id" AS "id","r"."id_evaluacion" AS "id_evaluacion","r"."nombre" AS "nombre","r"."cedula" AS "cedula","r"."whatsapp" AS "whatsapp","r"."empresa_contratante" AS "empresa_contratante","r"."cargo" AS "cargo","r"."id_cliente_conjunto" AS "id_cliente_conjunto","r"."acepta_tratamiento" AS "acepta_tratamiento","r"."respuestas" AS "respuestas","r"."calificacion" AS "calificacion","r"."created_at" AS "created_at","r"."updated_at" AS "updated_at","ei"."titulo" AS "titulo","c"."nombre_cliente" AS "nombre_cliente" from (("tbl_evaluacion_induccion_respuesta" "r" join "tbl_evaluacion_induccion" "ei" on(("r"."id_evaluacion" = "ei"."id"))) join "tbl_clientes" "c" on(("ei"."id_cliente" = "c"."id_cliente")));

-- Vista: v_tbl_evaluacion_simulacro
DROP VIEW IF EXISTS `v_tbl_evaluacion_simulacro`;
CREATE VIEW "v_tbl_evaluacion_simulacro" AS select "es"."id" AS "id","es"."id_cliente" AS "id_cliente","es"."fecha" AS "fecha","es"."direccion" AS "direccion","es"."evento_simulado" AS "evento_simulado","es"."alcance_simulacro" AS "alcance_simulacro","es"."tipo_evacuacion" AS "tipo_evacuacion","es"."personal_no_evacua" AS "personal_no_evacua","es"."tipo_alarma" AS "tipo_alarma","es"."distintivos_brigadistas" AS "distintivos_brigadistas","es"."puntos_encuentro" AS "puntos_encuentro","es"."recurso_humano" AS "recurso_humano","es"."equipos_emergencia" AS "equipos_emergencia","es"."nombre_brigadista_lider" AS "nombre_brigadista_lider","es"."email_brigadista_lider" AS "email_brigadista_lider","es"."whatsapp_brigadista_lider" AS "whatsapp_brigadista_lider","es"."imagen_1" AS "imagen_1","es"."imagen_2" AS "imagen_2","es"."hora_inicio" AS "hora_inicio","es"."alistamiento_recursos" AS "alistamiento_recursos","es"."asumir_roles" AS "asumir_roles","es"."suena_alarma" AS "suena_alarma","es"."distribucion_roles" AS "distribucion_roles","es"."llegada_punto_encuentro" AS "llegada_punto_encuentro","es"."agrupacion_por_afinidad" AS "agrupacion_por_afinidad","es"."conteo_personal" AS "conteo_personal","es"."agradecimiento_y_cierre" AS "agradecimiento_y_cierre","es"."tiempo_total" AS "tiempo_total","es"."alarma_efectiva" AS "alarma_efectiva","es"."orden_evacuacion" AS "orden_evacuacion","es"."liderazgo_brigadistas" AS "liderazgo_brigadistas","es"."organizacion_punto_encuentro" AS "organizacion_punto_encuentro","es"."participacion_general" AS "participacion_general","es"."evaluacion_cuantitativa" AS "evaluacion_cuantitativa","es"."evaluacion_cualitativa" AS "evaluacion_cualitativa","es"."observaciones" AS "observaciones","es"."hombre" AS "hombre","es"."mujer" AS "mujer","es"."ninos" AS "ninos","es"."adultos_mayores" AS "adultos_mayores","es"."discapacidad" AS "discapacidad","es"."mascotas" AS "mascotas","es"."total" AS "total","es"."estado" AS "estado","es"."ruta_pdf" AS "ruta_pdf","es"."created_at" AS "created_at","es"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente" from ("tbl_evaluacion_simulacro" "es" join "tbl_clientes" "c" on(("es"."id_cliente" = "c"."id_cliente")));

-- Vista: v_tbl_extintor_detalle
DROP VIEW IF EXISTS `v_tbl_extintor_detalle`;
CREATE VIEW "v_tbl_extintor_detalle" AS select "e"."id" AS "id","e"."id_inspeccion" AS "id_inspeccion","e"."pintura_cilindro" AS "pintura_cilindro","e"."golpes_extintor" AS "golpes_extintor","e"."autoadhesivo" AS "autoadhesivo","e"."manija_transporte" AS "manija_transporte","e"."palanca_accionamiento" AS "palanca_accionamiento","e"."presion" AS "presion","e"."manometro" AS "manometro","e"."boquilla" AS "boquilla","e"."manguera" AS "manguera","e"."ring_seguridad" AS "ring_seguridad","e"."senalizacion" AS "senalizacion","e"."soporte" AS "soporte","e"."fecha_vencimiento" AS "fecha_vencimiento","e"."foto" AS "foto","e"."observaciones" AS "observaciones","e"."orden" AS "orden","e"."created_at" AS "created_at","ie"."fecha_inspeccion" AS "fecha_inspeccion","c"."nombre_cliente" AS "nombre_cliente" from (("tbl_extintor_detalle" "e" join "tbl_inspeccion_extintores" "ie" on(("e"."id_inspeccion" = "ie"."id"))) join "tbl_clientes" "c" on(("ie"."id_cliente" = "c"."id_cliente")));

-- Vista: v_tbl_gabinete_detalle
DROP VIEW IF EXISTS `v_tbl_gabinete_detalle`;
CREATE VIEW "v_tbl_gabinete_detalle" AS select "g"."id" AS "id","g"."id_inspeccion" AS "id_inspeccion","g"."numero" AS "numero","g"."ubicacion" AS "ubicacion","g"."tiene_manguera" AS "tiene_manguera","g"."tiene_hacha" AS "tiene_hacha","g"."tiene_extintor" AS "tiene_extintor","g"."tiene_valvula" AS "tiene_valvula","g"."tiene_boquilla" AS "tiene_boquilla","g"."tiene_llave_spanner" AS "tiene_llave_spanner","g"."estado" AS "estado","g"."senalizacion" AS "senalizacion","g"."foto" AS "foto","g"."observaciones" AS "observaciones","g"."created_at" AS "created_at","ig"."fecha_inspeccion" AS "fecha_inspeccion","c"."nombre_cliente" AS "nombre_cliente" from (("tbl_gabinete_detalle" "g" join "tbl_inspeccion_gabinetes" "ig" on(("g"."id_inspeccion" = "ig"."id"))) join "tbl_clientes" "c" on(("ig"."id_cliente" = "c"."id_cliente")));

-- Vista: v_tbl_hallazgo_locativo
DROP VIEW IF EXISTS `v_tbl_hallazgo_locativo`;
CREATE VIEW "v_tbl_hallazgo_locativo" AS select "h"."id" AS "id","h"."id_inspeccion" AS "id_inspeccion","h"."descripcion" AS "descripcion","h"."imagen" AS "imagen","h"."imagen_correccion" AS "imagen_correccion","h"."fecha_hallazgo" AS "fecha_hallazgo","h"."fecha_correccion" AS "fecha_correccion","h"."estado" AS "estado","h"."observaciones" AS "observaciones","h"."orden" AS "orden","h"."created_at" AS "created_at","il"."fecha_inspeccion" AS "fecha_inspeccion","c"."nombre_cliente" AS "nombre_cliente" from (("tbl_hallazgo_locativo" "h" join "tbl_inspeccion_locativa" "il" on(("h"."id_inspeccion" = "il"."id"))) join "tbl_clientes" "c" on(("il"."id_cliente" = "c"."id_cliente")));

-- Vista: v_tbl_hv_brigadista
DROP VIEW IF EXISTS `v_tbl_hv_brigadista`;
CREATE VIEW "v_tbl_hv_brigadista" AS select "h"."id" AS "id","h"."id_cliente" AS "id_cliente","h"."fecha_registro" AS "fecha_registro","h"."fecha_inscripcion" AS "fecha_inscripcion","h"."foto_brigadista" AS "foto_brigadista","h"."nombre_completo" AS "nombre_completo","h"."documento_identidad" AS "documento_identidad","h"."f_nacimiento" AS "f_nacimiento","h"."email" AS "email","h"."telefono" AS "telefono","h"."direccion_residencia" AS "direccion_residencia","h"."edad" AS "edad","h"."eps" AS "eps","h"."peso" AS "peso","h"."estatura" AS "estatura","h"."rh" AS "rh","h"."estudios_1" AS "estudios_1","h"."lugar_estudio_1" AS "lugar_estudio_1","h"."anio_estudio_1" AS "anio_estudio_1","h"."estudios_2" AS "estudios_2","h"."lugar_estudio_2" AS "lugar_estudio_2","h"."anio_estudio_2" AS "anio_estudio_2","h"."estudios_3" AS "estudios_3","h"."lugar_estudio_3" AS "lugar_estudio_3","h"."anio_estudio_3" AS "anio_estudio_3","h"."enfermedades_importantes" AS "enfermedades_importantes","h"."medicamentos" AS "medicamentos","h"."cardiaca" AS "cardiaca","h"."pechoactividad" AS "pechoactividad","h"."dolorpecho" AS "dolorpecho","h"."conciencia" AS "conciencia","h"."huesos" AS "huesos","h"."medicamentos_bool" AS "medicamentos_bool","h"."actividadfisica" AS "actividadfisica","h"."convulsiones" AS "convulsiones","h"."vertigo" AS "vertigo","h"."oidos" AS "oidos","h"."lugarescerrados" AS "lugarescerrados","h"."miedoalturas" AS "miedoalturas","h"."haceejercicio" AS "haceejercicio","h"."miedo_ver_sangre" AS "miedo_ver_sangre","h"."restricciones_medicas" AS "restricciones_medicas","h"."deporte_semana" AS "deporte_semana","h"."firma" AS "firma","h"."estado" AS "estado","h"."ruta_pdf" AS "ruta_pdf","h"."created_at" AS "created_at","h"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente" from ("tbl_hv_brigadista" "h" join "tbl_clientes" "c" on(("h"."id_cliente" = "c"."id_cliente")));

-- Vista: v_tbl_informe_avances
DROP VIEW IF EXISTS `v_tbl_informe_avances`;
CREATE VIEW "v_tbl_informe_avances" AS select "ia"."id" AS "id","ia"."id_cliente" AS "id_cliente","ia"."id_consultor" AS "id_consultor","ia"."fecha_desde" AS "fecha_desde","ia"."fecha_hasta" AS "fecha_hasta","ia"."anio" AS "anio","ia"."puntaje_anterior" AS "puntaje_anterior","ia"."puntaje_actual" AS "puntaje_actual","ia"."diferencia_neta" AS "diferencia_neta","ia"."estado_avance" AS "estado_avance","ia"."indicador_plan_trabajo" AS "indicador_plan_trabajo","ia"."indicador_capacitacion" AS "indicador_capacitacion","ia"."img_cumplimiento_estandares" AS "img_cumplimiento_estandares","ia"."img_indicador_plan_trabajo" AS "img_indicador_plan_trabajo","ia"."img_indicador_capacitacion" AS "img_indicador_capacitacion","ia"."metricas_desglose_json" AS "metricas_desglose_json","ia"."resumen_avance" AS "resumen_avance","ia"."observaciones" AS "observaciones","ia"."actividades_abiertas" AS "actividades_abiertas","ia"."actividades_cerradas_periodo" AS "actividades_cerradas_periodo","ia"."enlace_dashboard" AS "enlace_dashboard","ia"."acta_visita_url" AS "acta_visita_url","ia"."soporte_1_texto" AS "soporte_1_texto","ia"."soporte_1_imagen" AS "soporte_1_imagen","ia"."soporte_2_texto" AS "soporte_2_texto","ia"."soporte_2_imagen" AS "soporte_2_imagen","ia"."soporte_3_texto" AS "soporte_3_texto","ia"."soporte_3_imagen" AS "soporte_3_imagen","ia"."soporte_4_texto" AS "soporte_4_texto","ia"."soporte_4_imagen" AS "soporte_4_imagen","ia"."ruta_pdf" AS "ruta_pdf","ia"."estado" AS "estado","ia"."created_at" AS "created_at","ia"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_informe_avances" "ia" join "tbl_clientes" "c" on(("ia"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("ia"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_inspeccion_botiquin
DROP VIEW IF EXISTS `v_tbl_inspeccion_botiquin`;
CREATE VIEW "v_tbl_inspeccion_botiquin" AS select "i"."id" AS "id","i"."id_cliente" AS "id_cliente","i"."id_consultor" AS "id_consultor","i"."fecha_inspeccion" AS "fecha_inspeccion","i"."ubicacion_botiquin" AS "ubicacion_botiquin","i"."foto_1" AS "foto_1","i"."foto_2" AS "foto_2","i"."instalado_pared" AS "instalado_pared","i"."libre_obstaculos" AS "libre_obstaculos","i"."lugar_visible" AS "lugar_visible","i"."con_senalizacion" AS "con_senalizacion","i"."tipo_botiquin" AS "tipo_botiquin","i"."estado_botiquin" AS "estado_botiquin","i"."foto_tabla_espinal" AS "foto_tabla_espinal","i"."obs_tabla_espinal" AS "obs_tabla_espinal","i"."estado_collares" AS "estado_collares","i"."foto_collares" AS "foto_collares","i"."estado_inmovilizadores" AS "estado_inmovilizadores","i"."foto_inmovilizadores" AS "foto_inmovilizadores","i"."recomendaciones" AS "recomendaciones","i"."pendientes_generados" AS "pendientes_generados","i"."ruta_pdf" AS "ruta_pdf","i"."estado" AS "estado","i"."created_at" AS "created_at","i"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_inspeccion_botiquin" "i" join "tbl_clientes" "c" on(("i"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("i"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_inspeccion_comunicaciones
DROP VIEW IF EXISTS `v_tbl_inspeccion_comunicaciones`;
CREATE VIEW "v_tbl_inspeccion_comunicaciones" AS select "i"."id" AS "id","i"."id_cliente" AS "id_cliente","i"."id_consultor" AS "id_consultor","i"."fecha_inspeccion" AS "fecha_inspeccion","i"."cant_telefono_fijo" AS "cant_telefono_fijo","i"."obs_telefono_fijo" AS "obs_telefono_fijo","i"."cant_telefonia_celular" AS "cant_telefonia_celular","i"."obs_telefonia_celular" AS "obs_telefonia_celular","i"."cant_radio_onda_corta" AS "cant_radio_onda_corta","i"."obs_radio_onda_corta" AS "obs_radio_onda_corta","i"."cant_software_citofonia" AS "cant_software_citofonia","i"."obs_software_citofonia" AS "obs_software_citofonia","i"."cant_megafonia" AS "cant_megafonia","i"."obs_megafonia" AS "obs_megafonia","i"."cant_cctv_audio" AS "cant_cctv_audio","i"."obs_cctv_audio" AS "obs_cctv_audio","i"."cant_alarma_comunicacion" AS "cant_alarma_comunicacion","i"."obs_alarma_comunicacion" AS "obs_alarma_comunicacion","i"."cant_voip" AS "cant_voip","i"."obs_voip" AS "obs_voip","i"."foto_1" AS "foto_1","i"."foto_2" AS "foto_2","i"."observaciones_finales" AS "observaciones_finales","i"."ruta_pdf" AS "ruta_pdf","i"."estado" AS "estado","i"."created_at" AS "created_at","i"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_inspeccion_comunicaciones" "i" join "tbl_clientes" "c" on(("i"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("i"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_inspeccion_extintores
DROP VIEW IF EXISTS `v_tbl_inspeccion_extintores`;
CREATE VIEW "v_tbl_inspeccion_extintores" AS select "i"."id" AS "id","i"."id_cliente" AS "id_cliente","i"."id_consultor" AS "id_consultor","i"."fecha_inspeccion" AS "fecha_inspeccion","i"."fecha_vencimiento_global" AS "fecha_vencimiento_global","i"."numero_extintores_totales" AS "numero_extintores_totales","i"."cantidad_abc" AS "cantidad_abc","i"."cantidad_co2" AS "cantidad_co2","i"."cantidad_solkaflam" AS "cantidad_solkaflam","i"."cantidad_agua" AS "cantidad_agua","i"."capacidad_libras" AS "capacidad_libras","i"."cantidad_unidades_residenciales" AS "cantidad_unidades_residenciales","i"."cantidad_porteria" AS "cantidad_porteria","i"."cantidad_oficina_admin" AS "cantidad_oficina_admin","i"."cantidad_shut_basuras" AS "cantidad_shut_basuras","i"."cantidad_salones_comunales" AS "cantidad_salones_comunales","i"."cantidad_cuarto_bombas" AS "cantidad_cuarto_bombas","i"."cantidad_planta_electrica" AS "cantidad_planta_electrica","i"."recomendaciones_generales" AS "recomendaciones_generales","i"."ruta_pdf" AS "ruta_pdf","i"."estado" AS "estado","i"."created_at" AS "created_at","i"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_inspeccion_extintores" "i" join "tbl_clientes" "c" on(("i"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("i"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_inspeccion_gabinetes
DROP VIEW IF EXISTS `v_tbl_inspeccion_gabinetes`;
CREATE VIEW "v_tbl_inspeccion_gabinetes" AS select "i"."id" AS "id","i"."id_cliente" AS "id_cliente","i"."id_consultor" AS "id_consultor","i"."fecha_inspeccion" AS "fecha_inspeccion","i"."tiene_gabinetes" AS "tiene_gabinetes","i"."entregados_constructora" AS "entregados_constructora","i"."cantidad_gabinetes" AS "cantidad_gabinetes","i"."elementos_gabinete" AS "elementos_gabinete","i"."ubicacion_gabinetes" AS "ubicacion_gabinetes","i"."estado_senalizacion_gab" AS "estado_senalizacion_gab","i"."foto_gab_1" AS "foto_gab_1","i"."foto_gab_2" AS "foto_gab_2","i"."observaciones_gabinetes" AS "observaciones_gabinetes","i"."tiene_detectores" AS "tiene_detectores","i"."detectores_entregados" AS "detectores_entregados","i"."cantidad_detectores" AS "cantidad_detectores","i"."ubicacion_detectores" AS "ubicacion_detectores","i"."foto_det_1" AS "foto_det_1","i"."foto_det_2" AS "foto_det_2","i"."observaciones_detectores" AS "observaciones_detectores","i"."ruta_pdf" AS "ruta_pdf","i"."estado" AS "estado","i"."created_at" AS "created_at","i"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_inspeccion_gabinetes" "i" join "tbl_clientes" "c" on(("i"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("i"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_inspeccion_locativa
DROP VIEW IF EXISTS `v_tbl_inspeccion_locativa`;
CREATE VIEW "v_tbl_inspeccion_locativa" AS select "i"."id" AS "id","i"."id_cliente" AS "id_cliente","i"."id_consultor" AS "id_consultor","i"."fecha_inspeccion" AS "fecha_inspeccion","i"."observaciones" AS "observaciones","i"."ruta_pdf" AS "ruta_pdf","i"."estado" AS "estado","i"."created_at" AS "created_at","i"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_inspeccion_locativa" "i" join "tbl_clientes" "c" on(("i"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("i"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_inspeccion_recursos_seguridad
DROP VIEW IF EXISTS `v_tbl_inspeccion_recursos_seguridad`;
CREATE VIEW "v_tbl_inspeccion_recursos_seguridad" AS select "i"."id" AS "id","i"."id_cliente" AS "id_cliente","i"."id_consultor" AS "id_consultor","i"."fecha_inspeccion" AS "fecha_inspeccion","i"."obs_lamparas" AS "obs_lamparas","i"."foto_lamparas" AS "foto_lamparas","i"."obs_antideslizantes" AS "obs_antideslizantes","i"."foto_antideslizantes" AS "foto_antideslizantes","i"."obs_pasamanos" AS "obs_pasamanos","i"."foto_pasamanos" AS "foto_pasamanos","i"."obs_vigilancia" AS "obs_vigilancia","i"."foto_vigilancia" AS "foto_vigilancia","i"."obs_iluminacion" AS "obs_iluminacion","i"."foto_iluminacion" AS "foto_iluminacion","i"."obs_planes_respuesta" AS "obs_planes_respuesta","i"."observaciones" AS "observaciones","i"."ruta_pdf" AS "ruta_pdf","i"."estado" AS "estado","i"."created_at" AS "created_at","i"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_inspeccion_recursos_seguridad" "i" join "tbl_clientes" "c" on(("i"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("i"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_inspeccion_senalizacion
DROP VIEW IF EXISTS `v_tbl_inspeccion_senalizacion`;
CREATE VIEW "v_tbl_inspeccion_senalizacion" AS select "i"."id" AS "id","i"."id_cliente" AS "id_cliente","i"."id_consultor" AS "id_consultor","i"."fecha_inspeccion" AS "fecha_inspeccion","i"."observaciones" AS "observaciones","i"."calificacion" AS "calificacion","i"."descripcion_cualitativa" AS "descripcion_cualitativa","i"."conteo_no_aplica" AS "conteo_no_aplica","i"."conteo_no_cumple" AS "conteo_no_cumple","i"."conteo_parcial" AS "conteo_parcial","i"."conteo_total" AS "conteo_total","i"."ruta_pdf" AS "ruta_pdf","i"."estado" AS "estado","i"."created_at" AS "created_at","i"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_inspeccion_senalizacion" "i" join "tbl_clientes" "c" on(("i"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("i"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_item_senalizacion
DROP VIEW IF EXISTS `v_tbl_item_senalizacion`;
CREATE VIEW "v_tbl_item_senalizacion" AS select "it"."id" AS "id","it"."id_inspeccion" AS "id_inspeccion","it"."nombre_item" AS "nombre_item","it"."grupo" AS "grupo","it"."estado_cumplimiento" AS "estado_cumplimiento","it"."foto" AS "foto","it"."orden" AS "orden","it"."created_at" AS "created_at","ins"."fecha_inspeccion" AS "fecha_inspeccion","c"."nombre_cliente" AS "nombre_cliente" from (("tbl_item_senalizacion" "it" join "tbl_inspeccion_senalizacion" "ins" on(("it"."id_inspeccion" = "ins"."id"))) join "tbl_clientes" "c" on(("ins"."id_cliente" = "c"."id_cliente")));

-- Vista: v_tbl_kpi_agua_potable
DROP VIEW IF EXISTS `v_tbl_kpi_agua_potable`;
CREATE VIEW "v_tbl_kpi_agua_potable" AS select "k"."id" AS "id","k"."id_cliente" AS "id_cliente","k"."id_consultor" AS "id_consultor","k"."fecha_inspeccion" AS "fecha_inspeccion","k"."nombre_responsable" AS "nombre_responsable","k"."indicador" AS "indicador","k"."cumplimiento" AS "cumplimiento","k"."valor_numerador" AS "valor_numerador","k"."valor_denominador" AS "valor_denominador","k"."calificacion_cualitativa" AS "calificacion_cualitativa","k"."observaciones" AS "observaciones","k"."registro_formato_1" AS "registro_formato_1","k"."registro_formato_2" AS "registro_formato_2","k"."registro_formato_3" AS "registro_formato_3","k"."registro_formato_4" AS "registro_formato_4","k"."ruta_pdf" AS "ruta_pdf","k"."estado" AS "estado","k"."created_at" AS "created_at","k"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_kpi_agua_potable" "k" join "tbl_clientes" "c" on(("k"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("k"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_kpi_limpieza
DROP VIEW IF EXISTS `v_tbl_kpi_limpieza`;
CREATE VIEW "v_tbl_kpi_limpieza" AS select "k"."id" AS "id","k"."id_cliente" AS "id_cliente","k"."id_consultor" AS "id_consultor","k"."fecha_inspeccion" AS "fecha_inspeccion","k"."nombre_responsable" AS "nombre_responsable","k"."indicador" AS "indicador","k"."cumplimiento" AS "cumplimiento","k"."valor_numerador" AS "valor_numerador","k"."valor_denominador" AS "valor_denominador","k"."calificacion_cualitativa" AS "calificacion_cualitativa","k"."observaciones" AS "observaciones","k"."registro_formato_1" AS "registro_formato_1","k"."registro_formato_2" AS "registro_formato_2","k"."registro_formato_3" AS "registro_formato_3","k"."registro_formato_4" AS "registro_formato_4","k"."ruta_pdf" AS "ruta_pdf","k"."estado" AS "estado","k"."created_at" AS "created_at","k"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_kpi_limpieza" "k" join "tbl_clientes" "c" on(("k"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("k"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_kpi_plagas
DROP VIEW IF EXISTS `v_tbl_kpi_plagas`;
CREATE VIEW "v_tbl_kpi_plagas" AS select "k"."id" AS "id","k"."id_cliente" AS "id_cliente","k"."id_consultor" AS "id_consultor","k"."fecha_inspeccion" AS "fecha_inspeccion","k"."nombre_responsable" AS "nombre_responsable","k"."indicador" AS "indicador","k"."cumplimiento" AS "cumplimiento","k"."valor_numerador" AS "valor_numerador","k"."valor_denominador" AS "valor_denominador","k"."calificacion_cualitativa" AS "calificacion_cualitativa","k"."observaciones" AS "observaciones","k"."registro_formato_1" AS "registro_formato_1","k"."registro_formato_2" AS "registro_formato_2","k"."registro_formato_3" AS "registro_formato_3","k"."registro_formato_4" AS "registro_formato_4","k"."ruta_pdf" AS "ruta_pdf","k"."estado" AS "estado","k"."created_at" AS "created_at","k"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_kpi_plagas" "k" join "tbl_clientes" "c" on(("k"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("k"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_kpi_residuos
DROP VIEW IF EXISTS `v_tbl_kpi_residuos`;
CREATE VIEW "v_tbl_kpi_residuos" AS select "k"."id" AS "id","k"."id_cliente" AS "id_cliente","k"."id_consultor" AS "id_consultor","k"."fecha_inspeccion" AS "fecha_inspeccion","k"."nombre_responsable" AS "nombre_responsable","k"."indicador" AS "indicador","k"."cumplimiento" AS "cumplimiento","k"."valor_numerador" AS "valor_numerador","k"."valor_denominador" AS "valor_denominador","k"."calificacion_cualitativa" AS "calificacion_cualitativa","k"."observaciones" AS "observaciones","k"."registro_formato_1" AS "registro_formato_1","k"."registro_formato_2" AS "registro_formato_2","k"."registro_formato_3" AS "registro_formato_3","k"."registro_formato_4" AS "registro_formato_4","k"."ruta_pdf" AS "ruta_pdf","k"."estado" AS "estado","k"."created_at" AS "created_at","k"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_kpi_residuos" "k" join "tbl_clientes" "c" on(("k"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("k"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_lookerstudio
DROP VIEW IF EXISTS `v_tbl_lookerstudio`;
CREATE VIEW "v_tbl_lookerstudio" AS select "l"."id_looker" AS "id_looker","l"."tipodedashboard" AS "tipodedashboard","l"."enlace" AS "enlace","l"."id_cliente" AS "id_cliente","l"."created_at" AS "created_at","l"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente" from ("tbl_lookerstudio" "l" join "tbl_clientes" "c" on(("l"."id_cliente" = "c"."id_cliente")));

-- Vista: v_tbl_matrices
DROP VIEW IF EXISTS `v_tbl_matrices`;
CREATE VIEW "v_tbl_matrices" AS select "m"."id_matriz" AS "id_matriz","m"."tipo" AS "tipo","m"."descripcion" AS "descripcion","m"."observaciones" AS "observaciones","m"."enlace" AS "enlace","m"."id_cliente" AS "id_cliente","m"."created_at" AS "created_at","m"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente" from ("tbl_matrices" "m" join "tbl_clientes" "c" on(("m"."id_cliente" = "c"."id_cliente")));

-- Vista: v_tbl_matriz_vulnerabilidad
DROP VIEW IF EXISTS `v_tbl_matriz_vulnerabilidad`;
CREATE VIEW "v_tbl_matriz_vulnerabilidad" AS select "m"."id" AS "id","m"."id_cliente" AS "id_cliente","m"."id_consultor" AS "id_consultor","m"."fecha_inspeccion" AS "fecha_inspeccion","m"."c1_plan_evacuacion" AS "c1_plan_evacuacion","m"."c2_alarma_evacuacion" AS "c2_alarma_evacuacion","m"."c3_ruta_evacuacion" AS "c3_ruta_evacuacion","m"."c4_visitantes_rutas" AS "c4_visitantes_rutas","m"."c5_puntos_reunion" AS "c5_puntos_reunion","m"."c6_puntos_reunion_2" AS "c6_puntos_reunion_2","m"."c7_senalizacion_evacuacion" AS "c7_senalizacion_evacuacion","m"."c8_rutas_evacuacion" AS "c8_rutas_evacuacion","m"."c9_ruta_principal" AS "c9_ruta_principal","m"."c10_senal_alarma" AS "c10_senal_alarma","m"."c11_sistema_deteccion" AS "c11_sistema_deteccion","m"."c12_iluminacion" AS "c12_iluminacion","m"."c13_iluminacion_emergencia" AS "c13_iluminacion_emergencia","m"."c14_sistema_contra_incendio" AS "c14_sistema_contra_incendio","m"."c15_extintores" AS "c15_extintores","m"."c16_divulgacion_plan" AS "c16_divulgacion_plan","m"."c17_coordinador_plan" AS "c17_coordinador_plan","m"."c18_brigada_emergencia" AS "c18_brigada_emergencia","m"."c19_simulacros" AS "c19_simulacros","m"."c20_entidades_socorro" AS "c20_entidades_socorro","m"."c21_ocupantes" AS "c21_ocupantes","m"."c22_plano_evacuacion" AS "c22_plano_evacuacion","m"."c23_rutas_circulacion" AS "c23_rutas_circulacion","m"."c24_puertas_salida" AS "c24_puertas_salida","m"."c25_estructura_construccion" AS "c25_estructura_construccion","m"."observaciones" AS "observaciones","m"."ruta_pdf" AS "ruta_pdf","m"."estado" AS "estado","m"."created_at" AS "created_at","m"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_matriz_vulnerabilidad" "m" join "tbl_clientes" "c" on(("m"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("m"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_pendientes
DROP VIEW IF EXISTS `v_tbl_pendientes`;
CREATE VIEW "v_tbl_pendientes" AS select "p"."id_pendientes" AS "id_pendientes","p"."id_cliente" AS "id_cliente","p"."id_acta" AS "id_acta","p"."responsable" AS "responsable","p"."tarea_actividad" AS "tarea_actividad","p"."fecha_asignacion" AS "fecha_asignacion","p"."fecha_cierre" AS "fecha_cierre","p"."estado" AS "estado","p"."estado_avance" AS "estado_avance","p"."evidencia_para_cerrarla" AS "evidencia_para_cerrarla","p"."conteo_dias" AS "conteo_dias","p"."created_at" AS "created_at","p"."updated_at" AS "updated_at","p"."id_acta_visita" AS "id_acta_visita","c"."nombre_cliente" AS "nombre_cliente","av"."fecha_visita" AS "fecha_visita" from (("tbl_pendientes" "p" join "tbl_clientes" "c" on(("p"."id_cliente" = "c"."id_cliente"))) left join "tbl_acta_visita" "av" on(("p"."id_acta_visita" = "av"."id")));

-- Vista: v_tbl_plan_emergencia
DROP VIEW IF EXISTS `v_tbl_plan_emergencia`;
CREATE VIEW "v_tbl_plan_emergencia" AS select "p"."id" AS "id","p"."id_cliente" AS "id_cliente","p"."id_consultor" AS "id_consultor","p"."fecha_visita" AS "fecha_visita","p"."foto_fachada" AS "foto_fachada","p"."foto_panorama" AS "foto_panorama","p"."casas_o_apartamentos" AS "casas_o_apartamentos","p"."sismo_resistente" AS "sismo_resistente","p"."anio_construccion" AS "anio_construccion","p"."numero_torres" AS "numero_torres","p"."numero_unidades_habitacionales" AS "numero_unidades_habitacionales","p"."casas_pisos" AS "casas_pisos","p"."foto_torres_1" AS "foto_torres_1","p"."foto_torres_2" AS "foto_torres_2","p"."parqueaderos_carros_residentes" AS "parqueaderos_carros_residentes","p"."parqueaderos_carros_visitantes" AS "parqueaderos_carros_visitantes","p"."parqueaderos_motos_residentes" AS "parqueaderos_motos_residentes","p"."parqueaderos_motos_visitantes" AS "parqueaderos_motos_visitantes","p"."hay_parqueadero_privado" AS "hay_parqueadero_privado","p"."foto_parqueaderos_carros" AS "foto_parqueaderos_carros","p"."foto_parqueaderos_motos" AS "foto_parqueaderos_motos","p"."cantidad_salones_comunales" AS "cantidad_salones_comunales","p"."cantidad_locales_comerciales" AS "cantidad_locales_comerciales","p"."tiene_oficina_admin" AS "tiene_oficina_admin","p"."foto_oficina_admin" AS "foto_oficina_admin","p"."tanque_agua" AS "tanque_agua","p"."planta_electrica" AS "planta_electrica","p"."circulacion_vehicular" AS "circulacion_vehicular","p"."foto_circulacion_vehicular" AS "foto_circulacion_vehicular","p"."circulacion_peatonal" AS "circulacion_peatonal","p"."foto_circulacion_peatonal_1" AS "foto_circulacion_peatonal_1","p"."foto_circulacion_peatonal_2" AS "foto_circulacion_peatonal_2","p"."salidas_emergencia" AS "salidas_emergencia","p"."foto_salida_emergencia_1" AS "foto_salida_emergencia_1","p"."foto_salida_emergencia_2" AS "foto_salida_emergencia_2","p"."ingresos_peatonales" AS "ingresos_peatonales","p"."foto_ingresos_peatonales" AS "foto_ingresos_peatonales","p"."accesos_vehiculares" AS "accesos_vehiculares","p"."foto_acceso_vehicular_1" AS "foto_acceso_vehicular_1","p"."foto_acceso_vehicular_2" AS "foto_acceso_vehicular_2","p"."concepto_entradas_salidas" AS "concepto_entradas_salidas","p"."hidrantes" AS "hidrantes","p"."cai_cercano" AS "cai_cercano","p"."bomberos_cercanos" AS "bomberos_cercanos","p"."proveedor_vigilancia" AS "proveedor_vigilancia","p"."proveedor_aseo" AS "proveedor_aseo","p"."otros_proveedores" AS "otros_proveedores","p"."registro_visitantes_forma" AS "registro_visitantes_forma","p"."registro_visitantes_emergencia" AS "registro_visitantes_emergencia","p"."cuenta_megafono" AS "cuenta_megafono","p"."ruta_evacuacion" AS "ruta_evacuacion","p"."mapa_evacuacion" AS "mapa_evacuacion","p"."foto_ruta_evacuacion_1" AS "foto_ruta_evacuacion_1","p"."foto_ruta_evacuacion_2" AS "foto_ruta_evacuacion_2","p"."puntos_encuentro" AS "puntos_encuentro","p"."foto_punto_encuentro_1" AS "foto_punto_encuentro_1","p"."foto_punto_encuentro_2" AS "foto_punto_encuentro_2","p"."sistema_alarma" AS "sistema_alarma","p"."codigos_alerta" AS "codigos_alerta","p"."energia_emergencia" AS "energia_emergencia","p"."deteccion_fuego" AS "deteccion_fuego","p"."vias_transito" AS "vias_transito","p"."nombre_administrador" AS "nombre_administrador","p"."horarios_administracion" AS "horarios_administracion","p"."personal_aseo" AS "personal_aseo","p"."personal_vigilancia" AS "personal_vigilancia","p"."ciudad" AS "ciudad","p"."cuadrante" AS "cuadrante","p"."tiene_gabinetes_hidraulico" AS "tiene_gabinetes_hidraulico","p"."ruta_residuos_solidos" AS "ruta_residuos_solidos","p"."empresa_aseo" AS "empresa_aseo","p"."servicios_sanitarios" AS "servicios_sanitarios","p"."frecuencia_basura" AS "frecuencia_basura","p"."detalle_mascotas" AS "detalle_mascotas","p"."detalle_dependencias" AS "detalle_dependencias","p"."observaciones" AS "observaciones","p"."ruta_pdf" AS "ruta_pdf","p"."estado" AS "estado","p"."created_at" AS "created_at","p"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_plan_emergencia" "p" join "tbl_clientes" "c" on(("p"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("p"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_plan_saneamiento
DROP VIEW IF EXISTS `v_tbl_plan_saneamiento`;
CREATE VIEW "v_tbl_plan_saneamiento" AS select "p"."id" AS "id","p"."id_cliente" AS "id_cliente","p"."id_consultor" AS "id_consultor","p"."fecha_programa" AS "fecha_programa","p"."nombre_responsable" AS "nombre_responsable","p"."ruta_pdf" AS "ruta_pdf","p"."estado" AS "estado","p"."created_at" AS "created_at","p"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_plan_saneamiento" "p" join "tbl_clientes" "c" on(("p"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("p"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_preparacion_simulacro
DROP VIEW IF EXISTS `v_tbl_preparacion_simulacro`;
CREATE VIEW "v_tbl_preparacion_simulacro" AS select "p"."id" AS "id","p"."id_cliente" AS "id_cliente","p"."id_consultor" AS "id_consultor","p"."fecha_simulacro" AS "fecha_simulacro","p"."ubicacion" AS "ubicacion","p"."direccion" AS "direccion","p"."evento_simulado" AS "evento_simulado","p"."alcance_simulacro" AS "alcance_simulacro","p"."tipo_evacuacion" AS "tipo_evacuacion","p"."personal_no_evacua" AS "personal_no_evacua","p"."tipo_alarma" AS "tipo_alarma","p"."distintivos_brigadistas" AS "distintivos_brigadistas","p"."puntos_encuentro" AS "puntos_encuentro","p"."recurso_humano" AS "recurso_humano","p"."equipos_emergencia" AS "equipos_emergencia","p"."nombre_brigadista_lider" AS "nombre_brigadista_lider","p"."email_brigadista_lider" AS "email_brigadista_lider","p"."whatsapp_brigadista_lider" AS "whatsapp_brigadista_lider","p"."entrega_formato_evaluacion" AS "entrega_formato_evaluacion","p"."imagen_1" AS "imagen_1","p"."imagen_2" AS "imagen_2","p"."hora_inicio" AS "hora_inicio","p"."alistamiento_recursos" AS "alistamiento_recursos","p"."asumir_roles" AS "asumir_roles","p"."suena_alarma" AS "suena_alarma","p"."distribucion_roles" AS "distribucion_roles","p"."llegada_punto_encuentro" AS "llegada_punto_encuentro","p"."agrupacion_por_afinidad" AS "agrupacion_por_afinidad","p"."conteo_personal" AS "conteo_personal","p"."agradecimiento_cierre" AS "agradecimiento_cierre","p"."observaciones" AS "observaciones","p"."ruta_pdf" AS "ruta_pdf","p"."estado" AS "estado","p"."created_at" AS "created_at","p"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_preparacion_simulacro" "p" join "tbl_clientes" "c" on(("p"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("p"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_presupuesto_detalle
DROP VIEW IF EXISTS `v_tbl_presupuesto_detalle`;
CREATE VIEW "v_tbl_presupuesto_detalle" AS select "d"."id_detalle" AS "id_detalle","d"."id_item" AS "id_item","d"."mes" AS "mes","d"."anio" AS "anio","d"."presupuestado" AS "presupuestado","d"."ejecutado" AS "ejecutado","d"."notas" AS "notas","d"."updated_at" AS "updated_at","pi"."actividad" AS "nombre_item","pc"."nombre" AS "nombre_categoria","ps"."anio" AS "anio_presupuesto","c"."nombre_cliente" AS "nombre_cliente" from (((("tbl_presupuesto_detalle" "d" join "tbl_presupuesto_items" "pi" on(("d"."id_item" = "pi"."id_item"))) join "tbl_presupuesto_sst" "ps" on(("pi"."id_presupuesto" = "ps"."id_presupuesto"))) join "tbl_clientes" "c" on(("ps"."id_cliente" = "c"."id_cliente"))) join "tbl_presupuesto_categorias" "pc" on(("pi"."id_categoria" = "pc"."id_categoria")));

-- Vista: v_tbl_presupuesto_items
DROP VIEW IF EXISTS `v_tbl_presupuesto_items`;
CREATE VIEW "v_tbl_presupuesto_items" AS select "pi"."id_item" AS "id_item","pi"."id_presupuesto" AS "id_presupuesto","pi"."id_categoria" AS "id_categoria","pi"."codigo_item" AS "codigo_item","pi"."actividad" AS "actividad","pi"."descripcion" AS "descripcion","pi"."orden" AS "orden","pi"."activo" AS "activo","pi"."created_at" AS "created_at","pi"."updated_at" AS "updated_at","ps"."anio" AS "anio","c"."nombre_cliente" AS "nombre_cliente","pc"."nombre" AS "nombre_categoria" from ((("tbl_presupuesto_items" "pi" join "tbl_presupuesto_sst" "ps" on(("pi"."id_presupuesto" = "ps"."id_presupuesto"))) join "tbl_clientes" "c" on(("ps"."id_cliente" = "c"."id_cliente"))) join "tbl_presupuesto_categorias" "pc" on(("pi"."id_categoria" = "pc"."id_categoria")));

-- Vista: v_tbl_presupuesto_sst
DROP VIEW IF EXISTS `v_tbl_presupuesto_sst`;
CREATE VIEW "v_tbl_presupuesto_sst" AS select "ps"."id_presupuesto" AS "id_presupuesto","ps"."id_cliente" AS "id_cliente","ps"."anio" AS "anio","ps"."mes_inicio" AS "mes_inicio","ps"."estado" AS "estado","ps"."observaciones" AS "observaciones","ps"."created_at" AS "created_at","ps"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente" from ("tbl_presupuesto_sst" "ps" join "tbl_clientes" "c" on(("ps"."id_cliente" = "c"."id_cliente")));

-- Vista: v_tbl_probabilidad_peligros
DROP VIEW IF EXISTS `v_tbl_probabilidad_peligros`;
CREATE VIEW "v_tbl_probabilidad_peligros" AS select "p"."id" AS "id","p"."id_cliente" AS "id_cliente","p"."id_consultor" AS "id_consultor","p"."fecha_inspeccion" AS "fecha_inspeccion","p"."sismos" AS "sismos","p"."inundaciones" AS "inundaciones","p"."vendavales" AS "vendavales","p"."atentados" AS "atentados","p"."asalto_hurto" AS "asalto_hurto","p"."vandalismo" AS "vandalismo","p"."incendios" AS "incendios","p"."explosiones" AS "explosiones","p"."inhalacion_gases" AS "inhalacion_gases","p"."falla_estructural" AS "falla_estructural","p"."intoxicacion_alimentos" AS "intoxicacion_alimentos","p"."densidad_poblacional" AS "densidad_poblacional","p"."observaciones" AS "observaciones","p"."ruta_pdf" AS "ruta_pdf","p"."estado" AS "estado","p"."created_at" AS "created_at","p"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_probabilidad_peligros" "p" join "tbl_clientes" "c" on(("p"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("p"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_programa_agua_potable
DROP VIEW IF EXISTS `v_tbl_programa_agua_potable`;
CREATE VIEW "v_tbl_programa_agua_potable" AS select "p"."id" AS "id","p"."id_cliente" AS "id_cliente","p"."id_consultor" AS "id_consultor","p"."fecha_programa" AS "fecha_programa","p"."nombre_responsable" AS "nombre_responsable","p"."cantidad_tanques" AS "cantidad_tanques","p"."capacidad_individual" AS "capacidad_individual","p"."capacidad_total" AS "capacidad_total","p"."ruta_pdf" AS "ruta_pdf","p"."estado" AS "estado","p"."created_at" AS "created_at","p"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_programa_agua_potable" "p" join "tbl_clientes" "c" on(("p"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("p"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_programa_limpieza
DROP VIEW IF EXISTS `v_tbl_programa_limpieza`;
CREATE VIEW "v_tbl_programa_limpieza" AS select "p"."id" AS "id","p"."id_cliente" AS "id_cliente","p"."id_consultor" AS "id_consultor","p"."fecha_programa" AS "fecha_programa","p"."nombre_responsable" AS "nombre_responsable","p"."ruta_pdf" AS "ruta_pdf","p"."estado" AS "estado","p"."created_at" AS "created_at","p"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_programa_limpieza" "p" join "tbl_clientes" "c" on(("p"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("p"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_programa_plagas
DROP VIEW IF EXISTS `v_tbl_programa_plagas`;
CREATE VIEW "v_tbl_programa_plagas" AS select "p"."id" AS "id","p"."id_cliente" AS "id_cliente","p"."id_consultor" AS "id_consultor","p"."fecha_programa" AS "fecha_programa","p"."nombre_responsable" AS "nombre_responsable","p"."ruta_pdf" AS "ruta_pdf","p"."estado" AS "estado","p"."created_at" AS "created_at","p"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_programa_plagas" "p" join "tbl_clientes" "c" on(("p"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("p"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_programa_residuos
DROP VIEW IF EXISTS `v_tbl_programa_residuos`;
CREATE VIEW "v_tbl_programa_residuos" AS select "p"."id" AS "id","p"."id_cliente" AS "id_cliente","p"."id_consultor" AS "id_consultor","p"."fecha_programa" AS "fecha_programa","p"."nombre_responsable" AS "nombre_responsable","p"."ruta_pdf" AS "ruta_pdf","p"."estado" AS "estado","p"."created_at" AS "created_at","p"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor" from (("tbl_programa_residuos" "p" join "tbl_clientes" "c" on(("p"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("p"."id_consultor" = "con"."id_consultor")));

-- Vista: v_tbl_pta_cliente
DROP VIEW IF EXISTS `v_tbl_pta_cliente`;
CREATE VIEW "v_tbl_pta_cliente" AS select "pt"."id_ptacliente" AS "id_ptacliente","pt"."id_cliente" AS "id_cliente","pt"."tipo_servicio" AS "tipo_servicio","pt"."phva_plandetrabajo" AS "phva_plandetrabajo","pt"."numeral_plandetrabajo" AS "numeral_plandetrabajo","pt"."actividad_plandetrabajo" AS "actividad_plandetrabajo","pt"."responsable_sugerido_plandetrabajo" AS "responsable_sugerido_plandetrabajo","pt"."fecha_propuesta" AS "fecha_propuesta","pt"."fecha_cierre" AS "fecha_cierre","pt"."responsable_definido_paralaactividad" AS "responsable_definido_paralaactividad","pt"."estado_actividad" AS "estado_actividad","pt"."porcentaje_avance" AS "porcentaje_avance","pt"."semana" AS "semana","pt"."observaciones" AS "observaciones","pt"."created_at" AS "created_at","pt"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente" from ("tbl_pta_cliente" "pt" join "tbl_clientes" "c" on(("pt"."id_cliente" = "c"."id_cliente")));

-- Vista: v_tbl_pta_cliente_audit
DROP VIEW IF EXISTS `v_tbl_pta_cliente_audit`;
CREATE VIEW "v_tbl_pta_cliente_audit" AS select "a"."id_audit" AS "id_audit","a"."id_ptacliente" AS "id_ptacliente","a"."id_cliente" AS "id_cliente","a"."accion" AS "accion","a"."campo_modificado" AS "campo_modificado","a"."valor_anterior" AS "valor_anterior","a"."valor_nuevo" AS "valor_nuevo","a"."id_usuario" AS "id_usuario","a"."nombre_usuario" AS "nombre_usuario","a"."email_usuario" AS "email_usuario","a"."rol_usuario" AS "rol_usuario","a"."ip_address" AS "ip_address","a"."user_agent" AS "user_agent","a"."metodo" AS "metodo","a"."descripcion" AS "descripcion","a"."fecha_accion" AS "fecha_accion","c"."nombre_cliente" AS "nombre_cliente","pta"."actividad_plandetrabajo" AS "actividad_plandetrabajo","u"."nombre_completo" AS "nombre_usuario_actual" from ((("tbl_pta_cliente_audit" "a" left join "tbl_clientes" "c" on(("a"."id_cliente" = "c"."id_cliente"))) left join "tbl_pta_cliente" "pta" on(("a"."id_ptacliente" = "pta"."id_ptacliente"))) left join "tbl_usuarios" "u" on(("a"."id_usuario" = "u"."id_usuario")));

-- Vista: v_tbl_pta_cliente_old
DROP VIEW IF EXISTS `v_tbl_pta_cliente_old`;
CREATE VIEW "v_tbl_pta_cliente_old" AS select "pt"."id_ptacliente" AS "id_ptacliente","pt"."id_cliente" AS "id_cliente","pt"."tipo_servicio" AS "tipo_servicio","pt"."phva_plandetrabajo" AS "phva_plandetrabajo","pt"."numeral_plandetrabajo" AS "numeral_plandetrabajo","pt"."actividad_plandetrabajo" AS "actividad_plandetrabajo","pt"."responsable_sugerido_plandetrabajo" AS "responsable_sugerido_plandetrabajo","pt"."fecha_propuesta" AS "fecha_propuesta","pt"."fecha_cierre" AS "fecha_cierre","pt"."responsable_definido_paralaactividad" AS "responsable_definido_paralaactividad","pt"."estado_actividad" AS "estado_actividad","pt"."porcentaje_avance" AS "porcentaje_avance","pt"."semana" AS "semana","pt"."observaciones" AS "observaciones","pt"."created_at" AS "created_at","pt"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente" from ("tbl_pta_cliente_old" "pt" join "tbl_clientes" "c" on(("pt"."id_cliente" = "c"."id_cliente")));

-- Vista: v_tbl_pta_transiciones
DROP VIEW IF EXISTS `v_tbl_pta_transiciones`;
CREATE VIEW "v_tbl_pta_transiciones" AS select "pt"."id_transicion" AS "id_transicion","pt"."id_ptacliente" AS "id_ptacliente","pt"."id_cliente" AS "id_cliente","pt"."estado_anterior" AS "estado_anterior","pt"."estado_nuevo" AS "estado_nuevo","pt"."id_usuario" AS "id_usuario","pt"."nombre_usuario" AS "nombre_usuario","pt"."fecha_transicion" AS "fecha_transicion","c"."nombre_cliente" AS "nombre_cliente","pta"."actividad_plandetrabajo" AS "actividad_plandetrabajo","u"."nombre_completo" AS "nombre_usuario_actual" from ((("tbl_pta_transiciones" "pt" join "tbl_clientes" "c" on(("pt"."id_cliente" = "c"."id_cliente"))) join "tbl_pta_cliente" "pta" on(("pt"."id_ptacliente" = "pta"."id_ptacliente"))) left join "tbl_usuarios" "u" on(("pt"."id_usuario" = "u"."id_usuario")));

-- Vista: v_tbl_reporte
DROP VIEW IF EXISTS `v_tbl_reporte`;
CREATE VIEW "v_tbl_reporte" AS select "r"."id_reporte" AS "id_reporte","r"."titulo_reporte" AS "titulo_reporte","r"."id_detailreport" AS "id_detailreport","r"."enlace" AS "enlace","r"."estado" AS "estado","r"."observaciones" AS "observaciones","r"."id_cliente" AS "id_cliente","r"."id_consultor" AS "id_consultor","r"."created_at" AS "created_at","r"."updated_at" AS "updated_at","r"."id_report_type" AS "id_report_type","r"."report_url" AS "report_url","r"."tag" AS "tag","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor","dr"."detail_report" AS "tipo_detalle","rt"."report_type" AS "tipo_reporte" from (((("tbl_reporte" "r" join "tbl_clientes" "c" on(("r"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("r"."id_consultor" = "con"."id_consultor"))) join "detail_report" "dr" on(("r"."id_detailreport" = "dr"."id_detailreport"))) join "report_type_table" "rt" on(("r"."id_report_type" = "rt"."id_report_type")));

-- Vista: v_tbl_reporte_capacitacion
DROP VIEW IF EXISTS `v_tbl_reporte_capacitacion`;
CREATE VIEW "v_tbl_reporte_capacitacion" AS select "rc"."id" AS "id","rc"."id_cliente" AS "id_cliente","rc"."id_consultor" AS "id_consultor","rc"."id_cronograma_capacitacion" AS "id_cronograma_capacitacion","rc"."fecha_capacitacion" AS "fecha_capacitacion","rc"."nombre_capacitacion" AS "nombre_capacitacion","rc"."objetivo_capacitacion" AS "objetivo_capacitacion","rc"."perfil_asistentes" AS "perfil_asistentes","rc"."nombre_capacitador" AS "nombre_capacitador","rc"."horas_duracion" AS "horas_duracion","rc"."numero_asistentes" AS "numero_asistentes","rc"."numero_programados" AS "numero_programados","rc"."numero_evaluados" AS "numero_evaluados","rc"."promedio_calificaciones" AS "promedio_calificaciones","rc"."foto_listado_asistencia" AS "foto_listado_asistencia","rc"."foto_capacitacion" AS "foto_capacitacion","rc"."foto_evaluacion" AS "foto_evaluacion","rc"."foto_otros_1" AS "foto_otros_1","rc"."foto_otros_2" AS "foto_otros_2","rc"."observaciones" AS "observaciones","rc"."ruta_pdf" AS "ruta_pdf","rc"."estado" AS "estado","rc"."created_at" AS "created_at","rc"."updated_at" AS "updated_at","rc"."mostrar_evaluacion_induccion" AS "mostrar_evaluacion_induccion","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor","cc"."estado" AS "estado_cronograma" from ((("tbl_reporte_capacitacion" "rc" join "tbl_clientes" "c" on(("rc"."id_cliente" = "c"."id_cliente"))) left join "tbl_consultor" "con" on(("rc"."id_consultor" = "con"."id_consultor"))) left join "tbl_cronog_capacitacion" "cc" on(("rc"."id_cronograma_capacitacion" = "cc"."id_cronograma_capacitacion")));

-- Vista: v_tbl_sesiones_usuario
DROP VIEW IF EXISTS `v_tbl_sesiones_usuario`;
CREATE VIEW "v_tbl_sesiones_usuario" AS select "s"."id_sesion" AS "id_sesion","s"."id_usuario" AS "id_usuario","s"."inicio_sesion" AS "inicio_sesion","s"."fin_sesion" AS "fin_sesion","s"."duracion_segundos" AS "duracion_segundos","s"."ip_address" AS "ip_address","s"."user_agent" AS "user_agent","s"."estado" AS "estado","s"."created_at" AS "created_at","u"."nombre_completo" AS "nombre_completo","u"."email" AS "email","u"."tipo_usuario" AS "tipo_usuario" from ("tbl_sesiones_usuario" "s" join "tbl_usuarios" "u" on(("s"."id_usuario" = "u"."id_usuario")));

-- Vista: v_tbl_usuario_roles
DROP VIEW IF EXISTS `v_tbl_usuario_roles`;
CREATE VIEW "v_tbl_usuario_roles" AS select "ur"."id_usuario" AS "id_usuario","ur"."id_rol" AS "id_rol","ur"."created_at" AS "created_at","u"."nombre_completo" AS "nombre_completo","u"."email" AS "email","u"."tipo_usuario" AS "tipo_usuario","r"."nombre_rol" AS "nombre_rol","r"."descripcion" AS "descripcion_rol" from (("tbl_usuario_roles" "ur" join "tbl_usuarios" "u" on(("ur"."id_usuario" = "u"."id_usuario"))) join "tbl_roles" "r" on(("ur"."id_rol" = "r"."id_rol")));

-- Vista: v_tbl_vencimientos_mantenimientos
DROP VIEW IF EXISTS `v_tbl_vencimientos_mantenimientos`;
CREATE VIEW "v_tbl_vencimientos_mantenimientos" AS select "v"."id_vencimientos_mmttos" AS "id_vencimientos_mmttos","v"."id_mantenimiento" AS "id_mantenimiento","v"."id_cliente" AS "id_cliente","v"."id_consultor" AS "id_consultor","v"."fecha_vencimiento" AS "fecha_vencimiento","v"."estado_actividad" AS "estado_actividad","v"."fecha_realizacion" AS "fecha_realizacion","v"."observaciones" AS "observaciones","c"."nombre_cliente" AS "nombre_cliente","con"."nombre_consultor" AS "nombre_consultor","m"."detalle_mantenimiento" AS "nombre_mantenimiento" from ((("tbl_vencimientos_mantenimientos" "v" join "tbl_clientes" "c" on(("v"."id_cliente" = "c"."id_cliente"))) join "tbl_consultor" "con" on(("v"."id_consultor" = "con"."id_consultor"))) join "tbl_mantenimientos" "m" on(("v"."id_mantenimiento" = "m"."id_mantenimiento")));

-- Vista: v_tbl_vigias
DROP VIEW IF EXISTS `v_tbl_vigias`;
CREATE VIEW "v_tbl_vigias" AS select "v"."id_vigia" AS "id_vigia","v"."nombre_vigia" AS "nombre_vigia","v"."cedula_vigia" AS "cedula_vigia","v"."periodo_texto" AS "periodo_texto","v"."firma_vigia" AS "firma_vigia","v"."observaciones" AS "observaciones","v"."id_cliente" AS "id_cliente","v"."created_at" AS "created_at","v"."updated_at" AS "updated_at","c"."nombre_cliente" AS "nombre_cliente" from ("tbl_vigias" "v" join "tbl_clientes" "c" on(("v"."id_cliente" = "c"."id_cliente")));

-- Vista: view_clientes_consultores
DROP VIEW IF EXISTS `view_clientes_consultores`;
CREATE VIEW "view_clientes_consultores" AS select "c"."id_cliente" AS "id_cliente","c"."nombre_cliente" AS "nombre_cliente","c"."id_consultor" AS "id_consultor","co"."nombre_consultor" AS "nombre_consultor","co"."correo_consultor" AS "correo_consultor" from ("tbl_clientes" "c" join "tbl_consultor" "co" on(("c"."id_consultor" = "co"."id_consultor")));

-- Vista: vista_cronograma_capacitaciones
DROP VIEW IF EXISTS `vista_cronograma_capacitaciones`;
CREATE VIEW "vista_cronograma_capacitaciones" AS select "cc"."id_cronograma_capacitacion" AS "id_cronograma_capacitacion","cs"."capacitacion" AS "capacitacion","cs"."objetivo_capacitacion" AS "objetivo_capacitacion","cs"."observaciones" AS "observaciones_capacitacion","cc"."id_cliente" AS "id_cliente","cc"."fecha_programada" AS "fecha_programada","cc"."fecha_de_realizacion" AS "fecha_de_realizacion","cc"."estado" AS "estado","cc"."perfil_de_asistentes" AS "perfil_de_asistentes","cc"."nombre_del_capacitador" AS "nombre_del_capacitador","cc"."horas_de_duracion_de_la_capacitacion" AS "horas_de_duracion_de_la_capacitacion","cc"."indicador_de_realizacion_de_la_capacitacion" AS "indicador_de_realizacion_de_la_capacitacion","cc"."numero_de_asistentes_a_capacitacion" AS "numero_de_asistentes_a_capacitacion","cc"."numero_total_de_personas_programadas" AS "numero_total_de_personas_programadas","cc"."porcentaje_cobertura" AS "porcentaje_cobertura","cc"."numero_de_personas_evaluadas" AS "numero_de_personas_evaluadas","cc"."promedio_de_calificaciones" AS "promedio_de_calificaciones","cc"."observaciones" AS "observaciones_cronograma" from ("tbl_cronog_capacitacion" "cc" join "capacitaciones_sst" "cs" on(("cc"."id_capacitacion" = "cs"."id_capacitacion")));

-- Vista: vw_consumo_usuarios
DROP VIEW IF EXISTS `vw_consumo_usuarios`;
CREATE VIEW "vw_consumo_usuarios" AS select "u"."id_usuario" AS "id_usuario","u"."nombre_completo" AS "nombre_completo","u"."email" AS "email","u"."tipo_usuario" AS "tipo_usuario",count("s"."id_sesion") AS "total_sesiones",sum(coalesce("s"."duracion_segundos",0)) AS "tiempo_total_segundos",sec_to_time(sum(coalesce("s"."duracion_segundos",0))) AS "tiempo_total_formato",max("s"."inicio_sesion") AS "ultima_sesion",avg(coalesce("s"."duracion_segundos",0)) AS "promedio_duracion_segundos" from ("tbl_usuarios" "u" left join "tbl_sesiones_usuario" "s" on((("u"."id_usuario" = "s"."id_usuario") and ("s"."estado" <> 'activa')))) group by "u"."id_usuario","u"."nombre_completo","u"."email","u"."tipo_usuario";

-- Vista: vw_reporte_completo
DROP VIEW IF EXISTS `vw_reporte_completo`;
CREATE VIEW "vw_reporte_completo" AS select "r"."id_reporte" AS "id_reporte","r"."titulo_reporte" AS "titulo_reporte","r"."observaciones" AS "observaciones","r"."estado" AS "estado_reporte","c"."nombre_cliente" AS "nombre_cliente","c"."correo_cliente" AS "correo_cliente","rt"."report_type" AS "report_type","dr"."detail_report" AS "detail_report","r"."created_at" AS "created_at","r"."updated_at" AS "updated_at" from ((("tbl_reporte" "r" join "tbl_clientes" "c" on(("r"."id_cliente" = "c"."id_cliente"))) join "report_type_table" "rt" on(("r"."id_report_type" = "rt"."id_report_type"))) join "detail_report" "dr" on(("r"."id_detailreport" = "dr"."id_detailreport")));

