SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

CREATE TABLE IF NOT EXISTS `organizacao` (
  `id_organizacao` INT NULL DEFAULT NULL AUTO_INCREMENT,
  `nome` VARCHAR(120) NOT NULL,
  `ativo` TINYINT NOT NULL DEFAULT TRUE,
  `criado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_organizacao`),
  UNIQUE INDEX (`nome` ASC))
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `setor` (
  `id_setor` INT NULL DEFAULT NULL AUTO_INCREMENT,
  `id_organizacao` INT NOT NULL,
  `nome` VARCHAR(80) NOT NULL,
  PRIMARY KEY (`id_setor`),
  UNIQUE INDEX `uk_setor_org` (`id_organizacao` ASC, `nome` ASC),
  CONSTRAINT `fk_setor_org`
    FOREIGN KEY (`id_organizacao`)
    REFERENCES `organizacao` (`id_organizacao`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `tipo_utilizador` (
  `id_tipo_utilizador` INT NULL DEFAULT NULL AUTO_INCREMENT,
  `nome` ENUM('FUNCIONARIO', 'DIRETOR_TECNICO', 'DIRETORA_EXECUTIVA') NOT NULL,
  PRIMARY KEY (`id_tipo_utilizador`),
  UNIQUE INDEX (`nome` ASC))
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `utilizador` (
  `id_utilizador` INT NULL DEFAULT NULL AUTO_INCREMENT,
  `id_setor` INT NOT NULL,
  `id_tipo_utilizador` INT NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `nome` VARCHAR(150) NOT NULL,
  `ativo` TINYINT NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`id_utilizador`),
  UNIQUE INDEX (`email` ASC),
  INDEX (`id_setor` ASC),
  INDEX (`id_tipo_utilizador` ASC),
  CONSTRAINT `fk_util_setor`
    FOREIGN KEY (`id_setor`)
    REFERENCES `setor` (`id_setor`),
  CONSTRAINT `fk_util_tipo`
    FOREIGN KEY (`id_tipo_utilizador`)
    REFERENCES `tipo_utilizador` (`id_tipo_utilizador`))
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `tipo_pedido` (
  `id_tipo_pedido` INT NULL DEFAULT NULL AUTO_INCREMENT,
  `nome` VARCHAR(120) NOT NULL,
  `ativo` TINYINT NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`id_tipo_pedido`))
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `estado_pedido` (
  `id_estado_pedido` INT NULL DEFAULT NULL AUTO_INCREMENT,
  `nome` VARCHAR(60) NOT NULL,
  PRIMARY KEY (`id_estado_pedido`))
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `periodo` (
  `id_periodo` INT NULL DEFAULT NULL AUTO_INCREMENT,
  `data_inicio` DATE NOT NULL,
  `data_fim` DATE NOT NULL,
  PRIMARY KEY (`id_periodo`))
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `pedido` (
  `id_pedido` INT NULL DEFAULT NULL AUTO_INCREMENT,
  `id_utilizador` INT NOT NULL,
  `id_tipo_pedido` INT NOT NULL,
  `id_estado_pedido` INT NOT NULL,
  `data_criacao` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pedido`),
  INDEX `idx_user_data` (`id_utilizador` ASC, `data_criacao` ASC),
  INDEX `idx_estado` (`id_estado_pedido` ASC),
  INDEX (`id_tipo_pedido` ASC),
  CONSTRAINT `fk_pedido_util`
    FOREIGN KEY (`id_utilizador`)
    REFERENCES `utilizador` (`id_utilizador`),
  CONSTRAINT `fk_pedido_tipo`
    FOREIGN KEY (`id_tipo_pedido`)
    REFERENCES `tipo_pedido` (`id_tipo_pedido`),
  CONSTRAINT `fk_pedido_estado`
    FOREIGN KEY (`id_estado_pedido`)
    REFERENCES `estado_pedido` (`id_estado_pedido`))
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `anexo` (
  `id_anexo` INT NULL DEFAULT NULL AUTO_INCREMENT,
  `id_pedido` INT NOT NULL,
  `caminho` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id_anexo`),
  INDEX (`id_pedido` ASC),
  CONSTRAINT `fk_anexo_pedido`
    FOREIGN KEY (`id_pedido`)
    REFERENCES `pedido` (`id_pedido`)
    ON DELETE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `aprovacao_pedido` (
  `id_aprovacao` INT NULL DEFAULT NULL AUTO_INCREMENT,
  `id_pedido` INT NOT NULL,
  `id_aprovador` INT NOT NULL,
  `papel_aprovador` ENUM('COLEGA', 'DIRETOR_TECNICO', 'DIRETORA_EXECUTIVA') NOT NULL,
  `id_estado_pedido` INT NOT NULL,
  PRIMARY KEY (`id_aprovacao`),
  UNIQUE INDEX `uk_pedido_papel` (`id_pedido` ASC, `papel_aprovador` ASC),
  INDEX (`id_aprovador` ASC),
  INDEX (`id_estado_pedido` ASC),
  CONSTRAINT `fk_aprov_pedido`
    FOREIGN KEY (`id_pedido`)
    REFERENCES `pedido` (`id_pedido`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_aprov_aprovador`
    FOREIGN KEY (`id_aprovador`)
    REFERENCES `utilizador` (`id_utilizador`),
  CONSTRAINT `fk_aprov_estado`
    FOREIGN KEY (`id_estado_pedido`)
    REFERENCES `estado_pedido` (`id_estado_pedido`))
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `historico_pedido` (
  `id_historico` INT NULL DEFAULT NULL AUTO_INCREMENT,
  `id_pedido` INT NOT NULL,
  `id_utilizador_acao` INT NOT NULL,
  `id_estado_anterior` INT NULL DEFAULT NULL,
  `id_estado_novo` INT NOT NULL,
  `data_alteracao` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_historico`),
  INDEX (`id_pedido` ASC),
  INDEX (`id_utilizador_acao` ASC),
  INDEX (`id_estado_anterior` ASC),
  INDEX (`id_estado_novo` ASC),
  CONSTRAINT `fk_hist_pedido`
    FOREIGN KEY (`id_pedido`)
    REFERENCES `pedido` (`id_pedido`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_hist_util`
    FOREIGN KEY (`id_utilizador_acao`)
    REFERENCES `utilizador` (`id_utilizador`),
  CONSTRAINT `fk_hist_estado_ant`
    FOREIGN KEY (`id_estado_anterior`)
    REFERENCES `estado_pedido` (`id_estado_pedido`),
  CONSTRAINT `fk_hist_estado_novo`
    FOREIGN KEY (`id_estado_novo`)
    REFERENCES `estado_pedido` (`id_estado_pedido`))
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `pedido_justificacao_faltas` (
  `id_pedido` INT NULL DEFAULT NULL,
  `data_falta` DATE NOT NULL,
  `hora_falta` TIME NOT NULL,
  `motivo` TEXT NOT NULL,
  PRIMARY KEY (`id_pedido`),
  CONSTRAINT `fk_jf_pedido`
    FOREIGN KEY (`id_pedido`)
    REFERENCES `pedido` (`id_pedido`)
    ON DELETE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `pedido_marcacao_ferias` (
  `id_pedido` INT NULL DEFAULT NULL,
  `id_periodo` INT NOT NULL,
  `data_inicio` DATE NOT NULL,
  `numero_dias` SMALLINT NOT NULL,
  PRIMARY KEY (`id_pedido`),
  INDEX (`id_periodo` ASC),
  CONSTRAINT `fk_mf_pedido`
    FOREIGN KEY (`id_pedido`)
    REFERENCES `pedido` (`id_pedido`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_mf_periodo`
    FOREIGN KEY (`id_periodo`)
    REFERENCES `periodo` (`id_periodo`))
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `pedido_alteracao_ferias` (
  `id_pedido` INT NULL DEFAULT NULL,
  `id_periodo_atual` INT NOT NULL,
  `novo_inicio` DATE NOT NULL,
  `novo_fim` DATE NOT NULL,
  `numero_dias` SMALLINT NOT NULL,
  PRIMARY KEY (`id_pedido`),
  INDEX (`id_periodo_atual` ASC),
  CONSTRAINT `fk_af_pedido`
    FOREIGN KEY (`id_pedido`)
    REFERENCES `pedido` (`id_pedido`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_af_periodo`
    FOREIGN KEY (`id_periodo_atual`)
    REFERENCES `periodo` (`id_periodo`))
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `pedido_troca_horario` (
  `id_pedido` INT NULL DEFAULT NULL,
  `id_colega` INT NOT NULL,
  `id_setor_colega` INT NOT NULL,
  `data_troca` DATE NOT NULL,
  `horario_antigo_ini` TIME NOT NULL,
  `horario_antigo_fim` TIME NOT NULL,
  `horario_novo_ini` TIME NOT NULL,
  `horario_novo_fim` TIME NOT NULL,
  `motivo` TEXT NOT NULL,
  PRIMARY KEY (`id_pedido`),
  INDEX (`id_colega` ASC),
  INDEX (`id_setor_colega` ASC),
  CONSTRAINT `fk_th_pedido`
    FOREIGN KEY (`id_pedido`)
    REFERENCES `pedido` (`id_pedido`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_th_colega`
    FOREIGN KEY (`id_colega`)
    REFERENCES `utilizador` (`id_utilizador`),
  CONSTRAINT `fk_th_setor`
    FOREIGN KEY (`id_setor_colega`)
    REFERENCES `setor` (`id_setor`))
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `pedido_interrupcao_atividade` (
  `id_pedido` INT NULL DEFAULT NULL,
  `data_folga` DATE NOT NULL,
  `horario_folga` TIME NOT NULL,
  `data_trabalhada` DATE NOT NULL,
  `horario_trabalhado` TIME NOT NULL,
  PRIMARY KEY (`id_pedido`),
  CONSTRAINT `fk_ia_pedido`
    FOREIGN KEY (`id_pedido`)
    REFERENCES `pedido` (`id_pedido`)
    ON DELETE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `pedido_folga_aniversario` (
  `id_pedido` INT NULL DEFAULT NULL,
  `data_folga` DATE NOT NULL,
  `horario` TIME NOT NULL,
  `data_aniversario` DATE NOT NULL,
  PRIMARY KEY (`id_pedido`),
  CONSTRAINT `fk_fa_pedido`
    FOREIGN KEY (`id_pedido`)
    REFERENCES `pedido` (`id_pedido`)
    ON DELETE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `pedido_assiduidade` (
  `id_pedido` INT NULL DEFAULT NULL,
  `data_sem_picagem` DATE NOT NULL,
  `horario` TIME NOT NULL,
  PRIMARY KEY (`id_pedido`),
  CONSTRAINT `fk_ass_pedido`
    FOREIGN KEY (`id_pedido`)
    REFERENCES `pedido` (`id_pedido`)
    ON DELETE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `pedido_licenca_nojo` (
  `id_pedido` INT NULL DEFAULT NULL,
  `dias_ausencia` SMALLINT NOT NULL,
  `nome_falecido` VARCHAR(150) NOT NULL,
  `grau_parentesco` VARCHAR(60) NOT NULL,
  PRIMARY KEY (`id_pedido`),
  CONSTRAINT `fk_ln_pedido`
    FOREIGN KEY (`id_pedido`)
    REFERENCES `pedido` (`id_pedido`)
    ON DELETE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `pedido_formacao` (
  `id_pedido` INT NULL DEFAULT NULL,
  `data_formacao` DATE NOT NULL,
  `tema_formacao` VARCHAR(200) NOT NULL,
  PRIMARY KEY (`id_pedido`),
  CONSTRAINT `fk_form_pedido`
    FOREIGN KEY (`id_pedido`)
    REFERENCES `pedido` (`id_pedido`)
    ON DELETE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `pedido_motivos_academicos` (
  `id_pedido` INT NULL DEFAULT NULL,
  `data_ausencia` DATE NOT NULL,
  `motivo_academico` VARCHAR(200) NOT NULL,
  PRIMARY KEY (`id_pedido`),
  CONSTRAINT `fk_ma_pedido`
    FOREIGN KEY (`id_pedido`)
    REFERENCES `pedido` (`id_pedido`)
    ON DELETE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `pedido_horas_extras` (
  `id_pedido` INT NULL DEFAULT NULL,
  `data_horas` DATE NOT NULL,
  `numero_horas` DECIMAL(4,2) NOT NULL,
  `motivo` TEXT NOT NULL,
  PRIMARY KEY (`id_pedido`),
  CONSTRAINT `fk_he_pedido`
    FOREIGN KEY (`id_pedido`)
    REFERENCES `pedido` (`id_pedido`)
    ON DELETE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `pedido_comp_entrada_tardia` (
  `id_pedido` INT NULL DEFAULT NULL,
  `data_entrada_tardia` DATE NOT NULL,
  `horario` TIME NOT NULL,
  `num_horas_extras` DECIMAL(4,2) NOT NULL,
  `data_horas_extras` DATE NOT NULL,
  `motivo` TEXT NOT NULL,
  PRIMARY KEY (`id_pedido`),
  CONSTRAINT `fk_cet_pedido`
    FOREIGN KEY (`id_pedido`)
    REFERENCES `pedido` (`id_pedido`)
    ON DELETE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `pedido_comp_saida_antecipada` (
  `id_pedido` INT NULL DEFAULT NULL,
  `data_saida_antecipada` DATE NOT NULL,
  `horario` TIME NOT NULL,
  `num_horas_extras` DECIMAL(4,2) NOT NULL,
  `data_horas_extras` DATE NOT NULL,
  `motivo` TEXT NOT NULL,
  PRIMARY KEY (`id_pedido`),
  CONSTRAINT `fk_csa_pedido`
    FOREIGN KEY (`id_pedido`)
    REFERENCES `pedido` (`id_pedido`)
    ON DELETE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `pedido_troca_folga_instituicao` (
  `id_pedido` INT NULL DEFAULT NULL,
  `data_original` DATE NOT NULL,
  `horario_original` TIME NOT NULL,
  `nova_data` DATE NOT NULL,
  `motivo` TEXT NOT NULL,
  PRIMARY KEY (`id_pedido`),
  CONSTRAINT `fk_tfi_pedido`
    FOREIGN KEY (`id_pedido`)
    REFERENCES `pedido` (`id_pedido`)
    ON DELETE CASCADE)
ENGINE = InnoDB;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
