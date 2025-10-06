SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


CREATE TABLE `produtos` (
  `id_produto` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `descricao` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `produtos` (`id_produto`, `codigo`, `descricao`) VALUES
(3, '2224', 'CHESY 3009 - BD C/5KG'),
(4, '2899', 'CHESY RF-REM DE FERRUGEM - 504014RF GL C/5L'),
(5, '7800', 'CHESY ALCOOL ISOPROPILICO - 400ML/220G'),
(6, '1,02E+08', 'CHAPA DE POLIONDAS 4MM HANDTOP UV , ROUTER SCRIBA 2516 CORTE'),
(7, '1714', 'CHESY WAX NATURALE MAX - TB C/ 200KG'),
(8, '18697', 'GICLE ROSCA M6X0,75 COM FURO 0,75MM'),
(9, '1901', 'CHESY TEREX 318 - GL C/50L'),
(10, '20099', 'CHESY LACT AC 601287 - GL/27KG'),



CREATE TABLE `transbordos` (
  `id_transbordo` int(11) NOT NULL,
  `data_emissao` datetime NOT NULL DEFAULT current_timestamp(),
  `observacoes` text DEFAULT NULL,
  `status` enum('rascunho','emitido','recebido') DEFAULT 'rascunho',
  `id_usuario_emissor` int(11) NOT NULL,
  `id_usuario_receptor` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `transbordo_itens` (
  `id_item` int(11) NOT NULL,
  `id_transbordo` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `lote` varchar(100) DEFAULT NULL,
  `quantidade` decimal(10,2) NOT NULL,
  `qtd_pallets` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `login` varchar(50) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `setor` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `usuarios` (`id_usuario`, `nome`, `login`, `senha_hash`, `setor`) VALUES
(4, 'diego pcp', 'diego', '$2y$10$53McqKHqZTxAVGd0612dqu6Go3ucbZQKbzXmk3TD.3n1P5Dv2BMEC', 'Logística'),
(5, 'bernardo lima ss', 'ti', '$2y$10$xDfoBU0amXDTrLH6ndJnXetdKJKiJQf2NdC36zdUy7PsCHcyQaacy', 'TI'),
(6, 'joao gabriel', 'joaoss', '$2y$10$xjLRPlGfdVbkt9fSUHJnOe6XzaHzP2a5sMxALe8PdriduJo8ZHuLO', 'Logistica'),
(7, 'bernardo lima', 'bernardo', '$2y$10$VDeZbesWGx72j8QKD9pkfuTLz2eattDNPCmUYRNX9inGqpzbGFc7S', 'TI'),
(8, 'colaborador da silva ', 'colaborador 1', '$2y$10$BQ8nygi3q9T7PkTpNtW4veA7zLcWcjbbhID7JX04AJit1l50Kk.jS', 'Producao'),
(9, 'vou tester', 'testando', '$2y$10$mAb7kVOM7n7X0xW2RwwAcO5Qqb01v8.nG4pyu40xmSDwsfgzZjU6i', 'Producao'),
(11, 'admin teste', 'admin', '$2y$10$Wa6S4.ObR7ouwWPw8bB/TOaULyUHgn83yTEmG9XB0MrI6TsouWU56', 'admin'),
(12, 'usuario da logistica', 'logistica', '$2y$10$ZY57jHd0Wj2I3Xp6VYDFMeIL3f4VAHmOycJksPrpxTIcAgnehaMI2', 'logistica'),
(13, 'usuario producao', 'producao', '$2y$10$9z.qRCc.ekCvNZjZlf.T1e8lRPREnq1nJE4jgsswRmp9OlFRBtgYu', 'producao');

ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id_produto`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Índices de tabela `transbordos`
--
ALTER TABLE `transbordos`
  ADD PRIMARY KEY (`id_transbordo`),
  ADD KEY `id_usuario_emissor` (`id_usuario_emissor`),
  ADD KEY `id_usuario_receptor` (`id_usuario_receptor`);

--
-- Índices de tabela `transbordo_itens`
--
ALTER TABLE `transbordo_itens`
  ADD PRIMARY KEY (`id_item`),
  ADD KEY `id_transbordo` (`id_transbordo`),
  ADD KEY `id_produto` (`id_produto`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `login` (`login`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id_produto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2934;

--
-- AUTO_INCREMENT de tabela `transbordos`
--
ALTER TABLE `transbordos`
  MODIFY `id_transbordo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1013;

--
-- AUTO_INCREMENT de tabela `transbordo_itens`
--
ALTER TABLE `transbordo_itens`
  MODIFY `id_item` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `transbordos`
--
ALTER TABLE `transbordos`
  ADD CONSTRAINT `transbordos_ibfk_1` FOREIGN KEY (`id_usuario_emissor`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `transbordos_ibfk_2` FOREIGN KEY (`id_usuario_receptor`) REFERENCES `usuarios` (`id_usuario`);

--
-- Restrições para tabelas `transbordo_itens`
--
ALTER TABLE `transbordo_itens`
  ADD CONSTRAINT `transbordo_itens_ibfk_1` FOREIGN KEY (`id_transbordo`) REFERENCES `transbordos` (`id_transbordo`) ON DELETE CASCADE,
  ADD CONSTRAINT `transbordo_itens_ibfk_2` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id_produto`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
