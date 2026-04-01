<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [

    // =========================================================================
    // Plugin Meta
    // =========================================================================

    'SmartLink Manager' => 'SmartLink Manager',
    'Manage smart links, route users by device, and track engagement from one control panel workspace.' => 'Gerencie seus smart links, direcione usuários por dispositivo e acompanhe o engajamento em um único espaço de trabalho.',
    'Open SmartLink Manager' => 'Abrir SmartLink Manager',
    '{name} plugin loaded' => 'Plugin {name} carregado',
    '{displayName} caches' => 'Caches de {displayName}',

    // =========================================================================
    // Element Names
    // =========================================================================

    'Smart Link' => 'Smart Link',
    'smart link' => 'smart link',
    'smart links' => 'smart links',
    'New smart link' => 'Novo smart link',

    // =========================================================================
    // Permissions
    // =========================================================================

    'Manage {plural}' => 'Gerenciar {plural}',
    'Create {plural}' => 'Criar {plural}',
    'Edit {plural}' => 'Editar {plural}',
    'Delete {plural}' => 'Excluir {plural}',
    'View analytics' => 'Ver análises',
    'Export analytics' => 'Exportar análises',
    'Clear analytics' => 'Limpar análises',
    'Clear cache' => 'Limpar cache',
    'View logs' => 'Ver registros',
    'View system logs' => 'Ver registros do sistema',
    'Download system logs' => 'Baixar registros do sistema',
    'Manage settings' => 'Gerenciar configurações',

    // =========================================================================
    // Navigation & Breadcrumbs
    // =========================================================================

    'Links' => 'Links',
    'Analytics' => 'Análises',
    'Logs' => 'Registros',
    'Settings' => 'Configurações',
    'General' => 'Geral',
    'QR Code' => 'QR Code',
    'Redirect' => 'Redirecionamento',
    'Export' => 'Exportar',
    'Advanced' => 'Avançado',
    'Interface' => 'Interface',
    'Behavior' => 'Comportamento',
    'Integrations' => 'Integrações',
    'Cache' => 'Cache',
    'Field Layout' => 'Layout de campos',
    'Overview' => 'Visão geral',
    'Import/Export' => 'Importar/Exportar',

    // =========================================================================
    // General Settings
    // =========================================================================

    'General Settings' => 'Configurações gerais',
    'Plugin Name' => 'Nome do plugin',
    'The name of the plugin as it appears in the Control Panel menu' => 'O nome do plugin conforme aparece no menu do painel de controle',
    'Plugin Settings' => 'Configurações do plugin',
    'Log Level' => 'Nível de registro',
    'Choose what types of messages to log. Debug level requires devMode to be enabled.' => 'Escolha quais tipos de mensagens registrar. O nível de depuração exige que o devMode esteja habilitado.',
    'Error (Critical errors only)' => 'Erro (somente erros críticos)',
    'Warning (Errors and warnings)' => 'Aviso (erros e avisos)',
    'Info (General information)' => 'Informação (informações gerais)',
    'Debug (Detailed debugging)' => 'Debug (depuração detalhada)',
    'Logging Settings' => 'Configurações de registro',

    // Logs viewer (logging-library)
    'All Levels' => 'Todos os níveis',
    'Info' => 'Info',
    'Debug' => 'Debug',
    'Select File' => 'Selecionar arquivo',
    'Select Date' => 'Selecionar data',
    'All Sources' => 'Todas as fontes',
    'Search messages and context...' => 'Pesquisar mensagens e contexto...',
    'System Logs' => 'Registros do sistema',
    'System' => 'Sistema',
    'Current log level' => 'Nível de registro atual',
    'No log files found. Log files are created when plugin activities occur.' => 'Nenhum arquivo de registro encontrado. Os arquivos de registro são criados quando ocorrem atividades do plugin.',
    'No log entries found for the selected filters.' => 'Nenhuma entrada de registro encontrada para os filtros selecionados.',
    'No context data available.' => 'Nenhum dado de contexto disponível.',
    'Level' => 'Nível',
    'User' => 'Usuário',
    'Message' => 'Mensagem',
    'entry' => 'entrada',
    'entries' => 'entradas',
    'Available Logs' => 'Registros disponíveis',
    'Current File' => 'Arquivo atual',
    'Download File' => 'Baixar arquivo',
    'Log Location' => 'Localização do registro',
    'Current Level' => 'Nível atual',
    'Retention' => 'Retenção',
    'days' => 'dias',
    'Context' => 'Contexto',
    'Entries' => 'Entradas',
    'file' => 'arquivo',
    'files' => 'arquivos',

    // =========================================================================
    // Site Settings
    // =========================================================================

    'Site Settings' => 'Configurações do site',
    'Enabled Sites' => 'Sites habilitados',
    'Select which sites {pluginName} should be enabled for. Leave empty to enable for all sites.' => 'Selecione para quais sites o {pluginName} deve ser habilitado. Deixe vazio para habilitar em todos os sites.',

    // =========================================================================
    // URL Settings
    // =========================================================================

    'URL Settings' => 'Configurações de URL',
    'Smart Link URL Prefix' => 'Prefixo URL do Smart Link',
    '{singularName} URL Prefix' => 'Prefixo URL de {singularName}',
    'QR Code URL Prefix' => 'Prefixo URL do QR Code',
    'The URL prefix for {pluginName} (e.g., \'go\' creates /go/your-link)' => 'O prefixo URL para {pluginName} (ex.: \'go\' cria /go/seu-link). Limpe o cache de rotas após a alteração (php craft clear-caches/compiled-templates).',
    'The URL prefix for QR code pages (e.g., \'qr\' creates /qr/your-link/view or \'go/qr\' creates /go/qr/your-link/view)' => 'O prefixo URL para páginas de QR Code (ex.: \'qr\' cria /qr/seu-link/view ou \'go/qr\' cria /go/qr/seu-link/view)',
    'Clear routes cache after changing this (php craft clear-caches/compiled-templates).' => 'Limpe o cache de rotas após esta alteração (php craft clear-caches/compiled-templates).',
    'Smart Link Base URL' => 'URL base do Smart Link',
    '{singularName} Base URL' => 'URL base de {singularName}',
    'Optional absolute URL used for generated smart links and QR URLs. Leave empty to use each site\'s base URL.' => 'URL absoluta opcional para smart links gerados e URLs de QR. Deixe vazio para usar a URL base de cada site.',
    'Base URL for generated smart links and QR URLs. For multisite, you can use tokens: {siteHandle}, {siteId}, {siteUid} (e.g., https://go.example.com/{siteHandle}). Leave empty to use each site\'s base URL.' => 'URL base para smart links gerados e URLs de QR. Para multissite, você pode usar tokens: {siteHandle}, {siteId}, {siteUid} (ex.: https://go.example.com/{siteHandle}). Deixe vazio para usar a URL base de cada site.',
    'Base URL for {singularName} and QR code URLs. For multisite, you can use tokens: {siteHandle}, {siteId}, {siteUid} (e.g., https://go.example.com/{siteHandle}). Leave empty to use each site\'s base URL.' => 'URL base para {singularName} e URLs de QR Code. Para multissite, você pode usar tokens: {siteHandle}, {siteId}, {siteUid} (ex.: https://go.example.com/{siteHandle}). Deixe vazio para usar a URL base de cada site.',
    'Changing the URL prefix will break all existing {pluginName}. Only change this before creating your first {singularName}.' => 'Alterar o prefixo URL quebrará todos os {pluginName} existentes. Faça essa alteração somente antes de criar seu primeiro {singularName}.',
    'Multisite detected: <code>Smart Link Base URL</code> is set without a site token. Generated URLs may resolve to only one site. Use a tokenized URL like <code>https://go.example.com/{siteHandle}</code> to preserve site-specific routing.' => 'Multissite detectado: <code>Smart Link Base URL</code> está configurada sem token de site. As URLs geradas podem apontar para apenas um site. Use uma URL com token como <code>https://go.example.com/{siteHandle}</code> para preservar o roteamento por site.',
    'Multisite detected: <code>{singularName} Base URL</code> is set without a site token. Generated URLs may resolve to only one site. Use a tokenized URL like <code>https://go.example.com/{siteHandle}</code> to preserve site-specific routing.' => 'Multissite detectado: <code>{singularName} Base URL</code> está configurada sem token de site. As URLs geradas podem apontar para apenas um site. Use uma URL com token como <code>https://go.example.com/{siteHandle}</code> para preservar o roteamento por site.',
    'Use URL Prefix' => 'Usar prefixo URL',
    'Enable to generate {singularName} URLs as /{prefix}/{slug}. Disable to generate root URLs as /{slug}.' => 'Habilite para gerar URLs de {singularName} como /{prefix}/{slug}. Desabilite para gerar URLs raiz como /{slug}.',
    'Both {smartName} and {shortName} are set to root URLs (no prefix) and share at least one host. Redirect routes can collide (e.g., <code>/slug</code>), and QR routes can also collide when both plugins use the same QR prefix (e.g., <code>/qr/slug</code>).' => 'Tanto {smartName} quanto {shortName} estão configurados com URLs raiz (sem prefixo) e compartilham pelo menos um host. As rotas de redirecionamento podem colidir (ex.: <code>/slug</code>), e as rotas QR também podem colidir se ambos os plugins usarem o mesmo prefixo QR (ex.: <code>/qr/slug</code>).',
    'Both {smartName} and {shortName} are set to root URLs (no prefix). Host overlap could not be fully resolved from current settings/config, so redirect route collisions are possible. QR routes may also collide if both plugins use the same QR prefix.' => 'Tanto {smartName} quanto {shortName} estão configurados com URLs raiz (sem prefixo). A sobreposição de hosts não pôde ser totalmente resolvida a partir das configurações atuais, portanto, colisões de rotas de redirecionamento são possíveis. As rotas QR também podem colidir se ambos os plugins usarem o mesmo prefixo QR.',
    'URL Prefix is disabled. {singularName} URLs will be generated as root paths like <code>/your-link</code>.' => 'O prefixo URL está desabilitado. As URLs de {singularName} serão geradas como caminhos raiz como <code>/seu-link</code>.',
    'This is being overridden by the <code>usePrefix</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>usePrefix</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>slugPrefix</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>slugPrefix</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrPrefix</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>qrPrefix</code> em <code>config/smartlink-manager.php</code>.',

    // =========================================================================
    // Template Settings
    // =========================================================================

    'Template Settings' => 'Configurações de template',
    'Redirect Template' => 'Template de redirecionamento',
    'Custom Redirect Template' => 'Template de redirecionamento personalizado',
    'Template path in your templates/ folder. Leave empty to use the default path.' => 'Caminho do template na sua pasta templates/. Deixe vazio para usar o caminho padrão.',
    'Path to custom template in your templates/ folder (e.g., smartlink-manager/redirect)' => 'Caminho para o template personalizado na sua pasta templates/ (ex.: smartlink-manager/redirect)',
    'QR Code Template' => 'Template de QR Code',
    'Custom QR Code Template' => 'Template de QR Code personalizado',
    'Path to custom template in your templates/ folder (e.g., smartlink-manager/qr)' => 'Caminho para o template personalizado na sua pasta templates/ (ex.: smartlink-manager/qr)',
    'These templates must exist in your site\'s <code>templates/</code> folder. Copy the reference templates from <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/</code> to <code>templates/smartlink-manager/</code> and customize as needed.' => 'Esses templates devem existir na pasta <code>templates/</code> do seu site. Copie os templates de referência de <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/</code> para <code>templates/smartlink-manager/</code> e personalize conforme necessário.',

    // =========================================================================
    // Asset Settings
    // =========================================================================

    'Asset Settings' => 'Configurações de recursos',
    'Image Volume' => 'Volume de imagens',
    '{singularName} Image Volume' => 'Volume de imagens de {singularName}',
    'Which asset volume should be used for {singularName} images' => 'Qual volume de recursos deve ser usado para as imagens de {singularName}',
    'All asset volumes' => 'Todos os volumes de recursos',

    // =========================================================================
    // QR Code Settings — Appearance
    // =========================================================================

    'QR Code Settings' => 'Configurações de QR Code',
    'Appearance & Style' => 'Aparência e estilo',
    'Enable QR Code' => 'Habilitar QR Code',
    'Default QR Code Size' => 'Tamanho padrão do QR Code',
    'Default size in pixels for generated QR codes' => 'Tamanho padrão em pixels para os QR Codes gerados',
    'QR Code Color' => 'Cor do QR Code',
    'Default QR Code Color' => 'Cor padrão do QR Code',
    'Default QR Background Color' => 'Cor de fundo padrão do QR Code',
    'Background Color' => 'Cor de fundo',
    'Default QR Code Format' => 'Formato padrão do QR Code',
    'Default format for generated QR codes' => 'Formato padrão para os QR Codes gerados',
    'Override the default QR code format' => 'Substituir o formato padrão do QR Code',
    'Format' => 'Formato',
    'Use Default ({format|upper})' => 'Usar padrão ({format|upper})',
    'Color' => 'Cor',
    'Background' => 'Fundo',
    'Eye Color' => 'Cor dos marcadores',
    'Color for position markers (leave empty to use main color)' => 'Cor para os marcadores de posição (deixe vazio para usar a cor principal)',
    'Size' => 'Tamanho',

    // =========================================================================
    // QR Code Settings — Logo
    // =========================================================================

    'Logo Settings' => 'Configurações de logotipo',
    'Enable QR Code Logo' => 'Habilitar logotipo do QR Code',
    'Enable Logo Overlay' => 'Habilitar sobreposição de logotipo',
    'Add a logo in the center of QR codes' => 'Adicionar um logotipo no centro dos QR Codes',
    'Logo Volume' => 'Volume do logotipo',
    'Logo Asset Volume' => 'Volume de recursos do logotipo',
    'Which asset volume contains QR code logos. Save settings after changing this to update the logo selection below.' => 'Qual volume de recursos contém os logotipos dos QR Codes. Salve as configurações após alterar isso para atualizar a seleção de logotipo abaixo.',
    'Default Logo' => 'Logotipo padrão',
    'Default logo to use for QR codes (can be overridden per smart link)' => 'Logotipo padrão para usar nos QR Codes (pode ser substituído por smart link)',
    'Default logo is required when logo overlay is enabled.' => 'O logotipo padrão é obrigatório quando a sobreposição de logotipo está habilitada.',
    'Logo Size (%)' => 'Tamanho do logotipo (%)',
    'Logo Size' => 'Tamanho do logotipo',
    'Logo size as percentage of QR code (10-30%)' => 'Tamanho do logotipo como percentagem do QR Code (10–30%)',
    'Logo' => 'Logotipo',
    'Override the default QR code logo' => 'Substituir o logotipo padrão do QR Code',
    'Using default logo from settings (click to override)' => 'Usando o logotipo padrão das configurações (clique para substituir)',
    'Logo overlay only works with PNG format. SVG format does not support logos.' => 'A sobreposição de logotipo funciona apenas com o formato PNG. O formato SVG não suporta logotipos.',
    'Logo requires PNG format' => 'O logotipo requer o formato PNG',
    'Please save settings to apply the volume change to the logo selection field.' => 'Salve as configurações para aplicar a alteração de volume ao campo de seleção de logotipo.',
    'Please save to apply the volume change' => 'Salve para aplicar a alteração de volume',

    // =========================================================================
    // QR Code Settings — Technical
    // =========================================================================

    'Technical Options' => 'Opções técnicas',
    'Error Correction Level' => 'Nível de correção de erros',
    'Higher levels work better if QR code is damaged but create denser patterns' => 'Níveis mais altos funcionam melhor se o QR Code estiver danificado, mas criam padrões mais densos',
    'QR Code Margin' => 'Margem do QR Code',
    'Margin Size' => 'Tamanho da margem',
    'White space around QR code (0-10 modules)' => 'Espaço em branco ao redor do QR Code (0–10 módulos)',
    'Module Style' => 'Estilo dos módulos',
    'Shape of the QR code modules' => 'Forma dos módulos do QR Code',
    'Eye Style' => 'Estilo dos marcadores',
    'Shape of the position markers (corners)' => 'Forma dos marcadores de posição (cantos)',

    // =========================================================================
    // QR Code Settings — Downloads
    // =========================================================================

    'Download Settings' => 'Configurações de download',
    'Enable QR Code Downloads' => 'Habilitar downloads de QR Code',
    'Allow users to download QR codes' => 'Permitir que os usuários baixem QR Codes',
    'Download Filename Pattern' => 'Padrão de nome de arquivo de download',
    'Available variables: {slug}, {size}, {format}' => 'Variáveis disponíveis: {slug}, {size}, {format}',
    'Download QR Code' => 'Baixar QR Code',
    'Small (256px)' => 'Pequeno (256px)',
    'Medium (512px)' => 'Médio (512px)',
    'Large (1024px)' => 'Grande (1024px)',
    'Extra Large (2048px)' => 'Extra grande (2048px)',
    'Custom Size...' => 'Tamanho personalizado...',

    // =========================================================================
    // QR Code Settings — Actions & Preview
    // =========================================================================

    'QR Code Actions' => 'Ações do QR Code',
    'View QR Code' => 'Ver QR Code',
    'QR Code Image' => 'Imagem do QR Code',
    'QR Code Page' => 'Página do QR Code',
    'Reset to Defaults' => 'Redefinir para padrões',
    'Live Preview' => 'Pré-visualização ao vivo',
    'Preview' => 'Pré-visualização',
    'Click to view QR code image' => 'Clique para ver a imagem do QR Code',
    'Click to view QR code page' => 'Clique para ver a página do QR Code',
    'Toggle preview' => 'Alternar pré-visualização',
    'QR code settings reset to defaults' => 'Configurações do QR Code redefinidas para os padrões',
    'Performance & Caching' => 'Desempenho e cache',
    'Configure QR code caching to improve performance and reduce server load.' => 'Configure o cache de QR Code para melhorar o desempenho e reduzir a carga do servidor.',
    'Go to Cache Settings' => 'Ir para as configurações de cache',

    // =========================================================================
    // Behavior Settings
    // =========================================================================

    'Behavior Settings' => 'Configurações de comportamento',
    'Redirect Behavior' => 'Comportamento de redirecionamento',
    '404 Redirect URL' => 'URL de redirecionamento 404',
    'Where to redirect when a {singularName} is not found or disabled' => 'Para onde redirecionar quando um {singularName} não é encontrado ou está desabilitado',
    'Can be a relative path (/) or full URL (https://example.com)' => 'Pode ser um caminho relativo (/) ou uma URL completa (https://example.com)',

    // =========================================================================
    // Analytics Settings
    // =========================================================================

    'Analytics Settings' => 'Configurações de análises',
    'Enable Analytics' => 'Habilitar análises',
    'Track Analytics' => 'Rastrear análises',
    'Track clicks and visitor data for {pluginName}' => 'Rastrear cliques e dados de visitantes para {pluginName}',
    'When enabled, {pluginName} will track visitor interactions, device types, geographic data, and other analytics information.' => 'Quando habilitado, {pluginName} rastreará as interações dos visitantes, os tipos de dispositivos, os dados geográficos e outras informações analíticas.',
    'Are you sure you want to disable analytics tracking for this {singularName}? This {singularName} will no longer collect visitor data and interactions.' => 'Você tem certeza de que deseja desabilitar o rastreamento de análises para este {singularName}? Este {singularName} não coletará mais dados e interações de visitantes.',

    // =========================================================================
    // Analytics Settings — IP Privacy
    // =========================================================================

    'IP Address Privacy' => 'Privacidade do endereço IP',
    'Anonymize IP Addresses' => 'Anonimizar endereços IP',
    'Mask IP addresses before storage for maximum privacy. <strong>IPv4</strong>: masks last octet (192.168.1.123 → 192.168.1.0). <strong>IPv6</strong>: masks last 80 bits. <strong>Trade-off</strong>: Reduces unique visitor accuracy (users on same subnet counted as one visitor). Geo-location still works normally.' => 'Mascare os endereços IP antes do armazenamento para máxima privacidade. <strong>IPv4</strong>: mascara o último octeto (192.168.1.123 → 192.168.1.0). <strong>IPv6</strong>: mascara os últimos 80 bits. <strong>Compensação</strong>: reduz a precisão de visitantes únicos (usuários na mesma sub-rede contam como um visitante). A geolocalização ainda funciona normalmente.',
    'Privacy Levels' => 'Níveis de privacidade',
    'Enabled' => 'Habilitado',
    'default' => 'padrão',
    'Full IP hashed with salt (accurate unique visitors)' => 'IP completo com hash e salt (visitantes únicos precisos)',
    'Subnet masked + hashed with salt (maximum privacy, less accurate)' => 'Sub-rede mascarada + hash com salt (privacidade máxima, menos preciso)',

    // =========================================================================
    // Analytics Settings — Retention & Cleanup
    // =========================================================================

    'Analytics Retention (days)' => 'Retenção de análises (dias)',
    'Analytics Retention' => 'Retenção de análises',
    'How many days to keep analytics data (0 for unlimited, max 3650)' => 'Quantos dias manter os dados analíticos (0 para ilimitado, máx. 3650)',
    'Data Retention' => 'Retenção de dados',
    'Analytics Cleanup' => 'Limpeza de análises',
    'Analytics data older than {days} days will be automatically cleaned up daily.' => 'Os dados analíticos com mais de {days} dias serão limpos automaticamente todos os dias.',
    'Clean Up Now' => 'Limpar agora',
    'Are you sure you want to clean up old analytics data now?' => 'Você tem certeza de que deseja limpar os dados analíticos antigos agora?',
    'Unlimited Retention Warning' => 'Aviso de retenção ilimitada',
    'Warning' => 'Aviso',
    'Analytics data will be retained indefinitely. This could result in large database size, slower performance, and increased storage costs over time. Consider setting a retention period (recommended: 90-365 days) for production sites.' => 'Os dados analíticos serão mantidos indefinidamente. Isso pode resultar em banco de dados de grande porte, desempenho mais lento e custos de armazenamento maiores. Considere definir um período de retenção (recomendado: 90–365 dias) para sites em produção.',

    // =========================================================================
    // Geo Provider Settings (from base _partials/geo-settings, uses |t(pluginHandle))
    // =========================================================================

    'Geographic Detection' => 'Detecção geográfica',
    'Geographic Analytics' => 'Análises geográficas',
    'Geographic Distribution' => 'Distribuição geográfica',
    'Enable Geographic Detection' => 'Habilitar detecção geográfica',
    'Detect user location for analytics' => 'Detectar localização do usuário para análises',
    'View Geographic Details' => 'Ver detalhes geográficos',
    'Loading geographic data...' => 'Carregando dados geográficos...',

    // Geo provider partial (lindemannrock-base/_partials/geo-settings)
    'Geo Provider' => 'Provedor Geo',
    'Select the geo IP lookup provider. HTTPS providers recommended for privacy.' => 'Selecione o provedor de consulta IP geográfica. Provedores HTTPS recomendados para privacidade.',
    'ip-api.com (HTTP free, HTTPS paid)' => 'ip-api.com (HTTP gratuito, HTTPS pago)',
    'ipapi.co (HTTPS, 1k/day free)' => 'ipapi.co (HTTPS, 1k/dia gratuito)',
    'ipinfo.io (HTTPS, 50k/month free)' => 'ipinfo.io (HTTPS, 50k/mês gratuito)',
    'API Key' => 'Chave API',
    'Optional. Required for paid tiers (enables HTTPS for ip-api.com Pro).' => 'Opcional. Obrigatório para níveis pagos (habilita HTTPS para ip-api.com Pro).',
    'ip-api.com free tier uses HTTP. IP addresses will be transmitted unencrypted. Add an API key for HTTPS (Pro tier) or switch to ipapi.co/ipinfo.io.' => 'O nível gratuito do ip-api.com usa HTTP. Os endereços IP serão transmitidos sem criptografia. Adicione uma chave API para HTTPS (nível Pro) ou mude para ipapi.co/ipinfo.io.',
    'ip-api.com: HTTP free tier (45 requests/min). Add API key for HTTPS (Pro tier, $13/month). IP addresses transmitted unencrypted without API key.' => 'ip-api.com: nível gratuito HTTP (45 requisições/min). Adicione chave API para HTTPS (nível Pro, $13/mês). Endereços IP transmitidos sem criptografia sem chave API.',
    'ipapi.co: HTTPS with 1,000 free requests/day. API key optional (increases rate limits).' => 'ipapi.co: HTTPS com 1.000 requisições gratuitas/dia. Chave API opcional (aumenta os limites de taxa).',
    'ipinfo.io: HTTPS with 50,000 free requests/month. API key optional (increases rate limits).' => 'ipinfo.io: HTTPS com 50.000 requisições gratuitas/mês. Chave API opcional (aumenta os limites de taxa).',

    // IP salt error banner (from base partial)
    'error' => 'erro',
    'Configuration Required' => 'Configuração necessária',
    'IP hash salt is missing.' => 'O salt de hash IP está faltando.',
    'Analytics tracking requires a secure salt for privacy protection.' => 'O rastreamento de análises requer um salt seguro para proteção de privacidade.',
    'Run one of these commands in your terminal:' => 'Execute um destes comandos no seu terminal:',
    'Standard:' => 'Padrão:',
    'COPY' => 'COPIAR',
    'DDEV:' => 'DDEV:',
    'This will automatically add' => 'Isso adicionará automaticamente',
    'to your' => 'ao seu',
    'file.' => 'arquivo.',
    'Warning:' => 'Aviso:',
    'Copy the same salt to staging and production environments.' => 'Copie o mesmo salt para os ambientes de staging e produção.',
    'COPIED!' => 'COPIADO!',
    'Failed to copy to clipboard' => 'Falha ao copiar para a área de transferência',

    // =========================================================================
    // Device Detection Settings
    // =========================================================================

    'Cache Device Detection' => 'Cache de detecção de dispositivos',
    'Cache device detection results for better performance' => 'Armazenar em cache os resultados de detecção de dispositivos para melhor desempenho',
    'Device Detection Cache Duration (seconds)' => 'Duração do cache de detecção de dispositivos (segundos)',

    // =========================================================================
    // Language Detection Settings
    // =========================================================================

    'Language Detection Method' => 'Método de detecção de idioma',
    'How to detect user language preference' => 'Como detectar a preferência de idioma do usuário',
    'Language Detection' => 'Detecção de idioma',
    'Enable automatic language detection to redirect users based on their browser or location' => 'Habilitar detecção automática de idioma para redirecionar usuários com base no navegador ou localização',

    // =========================================================================
    // Cache Settings
    // =========================================================================

    'Cache Settings' => 'Configurações de cache',
    'Cache Storage Settings' => 'Configurações de armazenamento de cache',
    'Cache Storage Method' => 'Método de armazenamento de cache',
    'How to store cache data. Use Redis/Database for load-balanced or multi-server environments.' => 'Como armazenar os dados do cache. Use Redis/Banco de dados para ambientes de balanceamento de carga ou multi-servidor.',
    'File System (default, single server)' => 'Sistema de arquivos (padrão, servidor único)',
    'Redis/Database (load-balanced, multi-server, cloud hosting)' => 'Redis/Banco de dados (balanceamento de carga, multi-servidor, hospedagem em nuvem)',
    'QR Code Caching' => 'Cache de QR Code',
    'Enable QR Code Cache' => 'Habilitar cache de QR Code',
    'Cache generated QR codes for better performance' => 'Armazenar em cache os QR Codes gerados para melhor desempenho',
    'QR Code Cache Duration (seconds)' => 'Duração do cache de QR Code (segundos)',
    'QR Code Cache Duration' => 'Duração do cache de QR Code',
    'How long to cache generated QR codes (in seconds)' => 'Por quanto tempo armazenar em cache os QR Codes gerados (em segundos)',
    'Cache duration in seconds' => 'Duração do cache em segundos',
    'Min: 60 (1 minute), Max: 604800 (7 days)' => 'Mín.: 60 (1 minuto), Máx.: 604800 (7 dias)',
    'Caching' => 'Cache',
    'Device Detection Caching' => 'Cache de detecção de dispositivos',
    'Device Detection Cache Duration' => 'Duração do cache de detecção de dispositivos',
    'Device detection caching is only available when Analytics is enabled. Go to' => 'O cache de detecção de dispositivos só está disponível quando as análises estão habilitadas. Vá para',
    'to enable analytics.' => 'para habilitar as análises.',

    // =========================================================================
    // Export Settings
    // =========================================================================

    'Export Settings' => 'Configurações de exportação',
    'Analytics Export Options' => 'Opções de exportação de análises',
    'Include Disabled Links in Export' => 'Incluir links desabilitados na exportação',
    'Include Disabled {pluginName} in Export' => 'Incluir {pluginName} desabilitados na exportação',
    'When enabled, analytics exports will include data from disabled {pluginName}' => 'Quando habilitado, as exportações de análises incluirão dados de {pluginName} desabilitados',
    'Include Expired Links in Export' => 'Incluir links expirados na exportação',
    'Include Expired {pluginName} in Export' => 'Incluir {pluginName} expirados na exportação',
    'When enabled, analytics exports will include data from expired {pluginName}' => 'Quando habilitado, as exportações de análises incluirão dados de {pluginName} expirados',
    'Export as CSV' => 'Exportar como CSV',

    // =========================================================================
    // Interface Settings
    // =========================================================================

    'Interface Settings' => 'Configurações de interface',
    'Items Per Page' => 'Itens por página',
    'Number of {pluginName} to show per page' => 'Número de {pluginName} a exibir por página',
    'Allow Multiple' => 'Permitir múltiplos',
    'Whether to allow multiple {pluginName} to be selected' => 'Se múltiplos {pluginName} podem ser selecionados',
    'The maximum number of {pluginName} that can be selected.' => 'O número máximo de {pluginName} que podem ser selecionados.',
    'Which sources should be available to select {pluginName} from?' => 'Quais fontes devem estar disponíveis para selecionar {pluginName}?',

    // =========================================================================
    // Integration Settings
    // =========================================================================

    'Third-Party Integrations' => 'Integrações de terceiros',
    'Integrations Settings' => 'Configurações de integrações',
    'Integrate {pluginName} with third-party analytics and tracking services to push click events to Google Tag Manager, Google Analytics, and other platforms.' => 'Integre {pluginName} com serviços de análise e rastreamento de terceiros para enviar eventos de clique para o Google Tag Manager, Google Analytics e outras plataformas.',
    '{pluginName} Integration' => 'Integração de {pluginName}',
    'Installed & Active' => 'Instalado e ativo',
    'Installed but Disabled' => 'Instalado, mas desabilitado',
    'Not Installed' => 'Não instalado',
    'Install Plugin' => 'Instalar plugin',
    'Push {smartLinksName} click events to Google Tag Manager and analytics platforms for tracking redirects, button clicks, and QR code scans.' => 'Envie eventos de clique de {smartLinksName} para o Google Tag Manager e plataformas de análise para rastrear redirecionamentos, cliques em botões e leituras de QR Codes.',
    'Active Tracking Scripts' => 'Scripts de rastreamento ativos',
    'Scripts receiving {pluginName} events' => 'Scripts que recebem eventos de {pluginName}',
    'Note' => 'Nota',
    'No tracking scripts are currently configured in {pluginName}. Events will be queued but not sent until you configure GTM or Google Analytics in {pluginName}.' => 'Nenhum script de rastreamento está configurado atualmente em {pluginName}. Os eventos serão enfileirados, mas não enviados até que você configure o GTM ou o Google Analytics em {pluginName}.',
    'Configuration' => 'Configuração',
    'Tracking Events' => 'Eventos de rastreamento',
    'Select which events to send to {pluginName}' => 'Selecione quais eventos enviar para {pluginName}',
    'Auto-Redirects' => 'Redirecionamentos automáticos',
    'Mobile users automatically redirected' => 'Usuários móveis redirecionados automaticamente',
    'Button Clicks' => 'Cliques em botões',
    'Manual platform selection on landing page' => 'Seleção manual de plataforma na página de destino',
    'QR Code Scans' => 'Leituras de QR Code',
    'QR code accessed via ?src=qr parameter' => 'QR Code acessado via parâmetro ?src=qr',
    'Event Prefix' => 'Prefixo de evento',
    'Prefix for event names (e.g., \'smart_links_redirect\')' => 'Prefixo para nomes de eventos (ex.: \'smart_links_redirect\')',
    'Event Data Structure' => 'Estrutura de dados do evento',
    'Click to view the data layer event format' => 'Clique para ver o formato de evento da camada de dados',
    'How Events Are Sent' => 'Como os eventos são enviados',
    '{pluginName} pushes events to GTM or GA4 dataLayer only' => '{pluginName} envia eventos apenas para o dataLayer do GTM ou GA4',
    'Only Google Tag Manager and Google Analytics 4 support the dataLayer format in SEOmatic' => 'Apenas o Google Tag Manager e o Google Analytics 4 suportam o formato dataLayer no SEOmatic',
    'Use GTM to forward to other platforms' => 'Use GTM para encaminhar para outras plataformas',
    'Configure GTM triggers and tags to forward {pluginName} events to Facebook Pixel, LinkedIn, HubSpot, etc.' => 'Configure gatilhos e tags do GTM para encaminhar eventos de {pluginName} para Facebook Pixel, LinkedIn, HubSpot, etc.',
    'Events are only sent when analytics tracking is enabled both globally and per-link' => 'Os eventos só são enviados quando o rastreamento de análises está habilitado globalmente e por link',
    'Architecture' => 'Arquitetura',
    'Push {pluginName} events to SEOmatic\'s Google Tag Manager data layer for tracking in GTM and Google Analytics.' => 'Envie eventos de {pluginName} para a camada de dados do Google Tag Manager do SEOmatic para rastreamento no GTM e Google Analytics.',
    'Select which {pluginName} events to send to SEOmatic' => 'Selecione quais eventos de {pluginName} enviar para o SEOmatic',
    'Fathom, Matomo, and Plausible are shown above but do not receive events directly from {pluginName}' => 'Fathom, Matomo e Plausible são mostrados acima, mas não recebem eventos diretamente de {pluginName}',
    // Redirect Manager Integration
    'Create permanent redirect records when {pluginName} slugs change. Provides centralized redirect management and analytics tracking.' => 'Crie registros de redirecionamento permanentes quando os slugs de {pluginName} mudarem. Fornece gerenciamento centralizado de redirecionamentos e rastreamento analítico.',
    'Creates permanent redirects when {pluginName} slugs change or links are deleted' => 'Cria redirecionamentos permanentes quando os slugs de {pluginName} mudam ou os links são excluídos',
    'Automatic Redirect Creation' => 'Criação automática de redirecionamentos',
    'Select which events should create permanent redirects in {pluginName}' => 'Selecione quais eventos devem criar redirecionamentos permanentes em {pluginName}',
    'Slug Changes' => 'Alterações de slug',
    'Change slug from <code>promo-2024</code> to <code>promo-2025</code> → Creates <code>/go/promo-2024</code> → <code>/go/promo-2025</code>' => 'Alterar slug de <code>promo-2024</code> para <code>promo-2025</code> → Cria <code>/go/promo-2024</code> → <code>/go/promo-2025</code>',
    'Benefits of This Integration' => 'Benefícios desta integração',
    'Centralized Management' => 'Gerenciamento centralizado',
    'View and manage all redirects ({pluginName} + regular pages) in one place' => 'Veja e gerencie todos os redirecionamentos ({pluginName} + páginas regulares) em um único lugar',
    'Analytics Tracking' => 'Rastreamento analítico',
    'See how many people try to access deleted or changed {pluginName}, their devices, browsers, and countries' => 'Veja quantas pessoas tentam acessar {pluginName} excluídos ou alterados, seus dispositivos, navegadores e países',
    'Persistent Redirects' => 'Redirecionamentos persistentes',
    'Redirects persist even if {pluginName} is deleted, preventing broken links permanently' => 'Os redirecionamentos persistem mesmo que {pluginName} seja excluído, prevenindo permanentemente links quebrados',
    'Source Tracking' => 'Rastreamento de origem',
    '{rmPluginName} shows which plugin created each redirect for better organization' => '{rmPluginName} mostra qual plugin criou cada redirecionamento para melhor organização',
    'Enabled Integrations' => 'Integrações habilitadas',
    // SmartLinkType (Link field integration)
    '{pluginName} is not enabled for site "{site}". Enable it in plugin settings to use {pluginNameLower} here.' => '{pluginName} não está habilitado para o site "{site}". Habilite-o nas configurações do plugin para usar {pluginNameLower} aqui.',
    'Invalid {pluginName} format.' => 'Formato de {pluginName} inválido.',
    '{pluginName} not found.' => '{pluginName} não encontrado.',

    // =========================================================================
    // Smart Link Fields (edit page)
    // =========================================================================

    'Title' => 'Título',
    'The title of this {singularName}' => 'O título deste {singularName}',
    'Description' => 'Descrição',
    'A brief description of this {singularName}' => 'Uma breve descrição deste {singularName}',
    'Icon' => 'Ícone',
    'Icon identifier or URL for this {singularName}' => 'Identificador de ícone ou URL para este {singularName}',
    'Image' => 'Imagem',
    'Select an image for this {singularName}' => 'Selecione uma imagem para este {singularName}',
    'Image Size' => 'Tamanho da imagem',
    'Select the size for the {singularName} image' => 'Selecione o tamanho para a imagem de {singularName}',
    'Hide Title on Landing Pages' => 'Ocultar título nas páginas de destino',
    'Hide the {singularName} title on both redirect and QR code landing pages' => 'Ocultar o título de {singularName} nas páginas de destino de redirecionamento e QR Code',
    'Display Settings' => 'Configurações de exibição',
    'Advanced Settings' => 'Configurações avançadas',
    'Destination URL' => 'URL de destino',
    'Last Destination URL' => 'Última URL de destino',
    'Fallback URL' => 'URL alternativa',
    'The URL to redirect to when no platform-specific URL is available' => 'A URL para redirecionar quando nenhuma URL específica de plataforma está disponível',
    'iOS URL' => 'URL do iOS',
    'App Store URL for iOS devices' => 'URL da App Store para dispositivos iOS',
    'Android URL' => 'URL do Android',
    'Google Play Store URL for Android devices' => 'URL da Google Play Store para dispositivos Android',
    'Huawei URL' => 'URL da Huawei',
    'AppGallery URL for Huawei devices' => 'URL da AppGallery para dispositivos Huawei',
    'Amazon URL' => 'URL da Amazon',
    'Amazon Appstore URL' => 'URL da Amazon Appstore',
    'Windows URL' => 'URL do Windows',
    'Microsoft Store URL for Windows devices' => 'URL da Microsoft Store para dispositivos Windows',
    'Mac URL' => 'URL do Mac',
    'Mac App Store URL' => 'URL da Mac App Store',
    'App Store URLs' => 'URLs das lojas de aplicativos',
    'Enter the store URLs for each platform. The system will automatically redirect users to the appropriate store based on their device.' => 'Insira as URLs das lojas para cada plataforma. O sistema redirecionará automaticamente os usuários para a loja apropriada com base no dispositivo.',
    '{pluginName} URL' => 'URL de {pluginName}',
    'URL copied to clipboard' => 'URL copiada para a área de transferência',
    'New {singularName}' => 'Novo {singularName}',

    // =========================================================================
    // Field Layout
    // =========================================================================

    'Add custom fields to {singularName} elements. Any fields you add here will appear in the {singularName} edit screen.' => 'Adicione campos personalizados aos elementos {singularName}. Todos os campos adicionados aqui aparecerão na tela de edição de {singularName}.',
    'No field layout available.' => 'Nenhum layout de campo disponível.',

    // =========================================================================
    // Smart Link Element — Index & Actions
    // =========================================================================

    'Slug' => 'Slug',
    'Redirect Page' => 'Página de redirecionamento',
    'All {pluginName}' => 'Todos os {pluginName}',
    'New {name}' => 'Novo {name}',
    'Are you sure you want to delete the selected smart links?' => 'Você tem certeza de que deseja excluir os smart links selecionados?',
    'Smart links deleted.' => 'Smart links excluídos.',
    'Smart links restored.' => 'Smart links restaurados.',
    'Some smart links restored.' => 'Alguns smart links restaurados.',
    'Smart links not restored.' => 'Smart links não restaurados.',
    'Add a smart link' => 'Adicionar um smart link',
    'No smart links selected' => 'Nenhum smart link selecionado',
    'You can only select up to {limit} {limit, plural, =1{smart link} other{smart links}}.' => 'Você só pode selecionar até {limit} {limit, plural, =1{smart link} other{smart links}}.',
    'Create a new smart link' => 'Criar um novo smart link',

    // =========================================================================
    // Analytics Dashboard — Overview Tab
    // =========================================================================

    'View Analytics' => 'Ver análises',
    'Traffic Overview' => 'Visão geral do tráfego',
    'Traffic & Devices' => 'Tráfego e dispositivos',
    'Geographic' => 'Geográfico',
    'Total Links' => 'Total de links',
    'Active Links' => 'Links ativos',
    'Total Clicks' => 'Total de cliques',
    'total clicks' => 'total de cliques',
    'Clicks' => 'Cliques',
    'Unique Visitors' => 'Visitantes únicos',
    'Total Interactions' => 'Total de interações',
    'Avg. Clicks/Day' => 'Méd. cliques/dia',
    'Avg. Interactions/Day' => 'Méd. interações/dia',
    'Engagement Rate' => 'Taxa de engajamento',
    'Top {pluginName} (Top 20)' => 'Top {pluginName} (Top 20)',
    'Latest Interactions (Top 20)' => 'Últimas interações (Top 20)',
    'Interactions (Last 20)' => 'Interações (últimas 20)',
    'No analytics data yet' => 'Ainda não há dados analíticos',
    'Analytics will appear here once your {singularName} starts receiving clicks.' => 'As análises aparecerão aqui assim que seu {singularName} começar a receber cliques.',
    'Failed to load analytics data' => 'Falha ao carregar dados analíticos',
    'Failed to load countries data' => 'Falha ao carregar dados de países',
    'No data for selected period' => 'Nenhum dado para o período selecionado',

    // =========================================================================
    // Analytics Dashboard — Traffic & Devices Tab
    // =========================================================================

    'Device Analytics' => 'Análises de dispositivos',
    'Device Types' => 'Tipos de dispositivos',
    'Device Brands' => 'Marcas de dispositivos',
    'Operating Systems' => 'Sistemas operacionais',
    'Browser Usage' => 'Uso de navegadores',
    'Usage Patterns' => 'Padrões de uso',
    'Peak Usage Hours' => 'Horários de pico de uso',
    'Peak usage at {hour}' => 'Pico de uso às {hour}',
    'Daily Clicks' => 'Cliques diários',

    // =========================================================================
    // Analytics Dashboard — Geographic Tab
    // =========================================================================

    'Top Countries' => 'Principais países',
    'Top Cities' => 'Principais cidades',
    'Top Cities Worldwide' => 'Principais cidades mundiais',
    'No country data available' => 'Nenhum dado de país disponível',
    'No city data available' => 'Nenhum dado de cidade disponível',
    'Geographic detection is disabled.' => 'A detecção geográfica está desabilitada.',
    'Enable in Settings' => 'Habilitar nas configurações',

    // =========================================================================
    // Analytics Data — Table Columns & Labels
    // =========================================================================

    'Date' => 'Data',
    'Time' => 'Hora',
    'Device' => 'Dispositivo',
    'Location' => 'Localização',
    'Country' => 'País',
    'Countries' => 'Países',
    'City' => 'Cidade',
    'Site' => 'Site',
    'Source' => 'Fonte',
    'Type' => 'Tipo',
    'OS' => 'SO',
    'Operating System' => 'Sistema operacional',
    'Browser' => 'Navegador',
    'Interactions' => 'Interações',
    'Latest Interactions' => 'Últimas interações',
    'No interactions recorded yet' => 'Nenhuma interação registrada ainda',
    'Last Interaction' => 'Última interação',
    'Last Interaction Type' => 'Tipo da última interação',
    'Last Click' => 'Último clique',
    'Device information not available' => 'Informações do dispositivo não disponíveis',
    'OS information not available' => 'Informações do SO não disponíveis',
    'Name' => 'Nome',
    'Percentage' => 'Porcentagem',

    // =========================================================================
    // Analytics Dashboard — JS strings (passed to JavaScript)
    // =========================================================================

    'No interaction data available for the selected filters.' => 'Nenhum dado de interação disponível para os filtros selecionados.',
    'No device data available for the selected filters.' => 'Nenhum dado de dispositivo disponível para os filtros selecionados.',
    'No device brand data available for the selected filters.' => 'Nenhum dado de marca de dispositivo disponível para os filtros selecionados.',
    'No OS data available for the selected filters.' => 'Nenhum dado de SO disponível para os filtros selecionados.',
    'No browser data available for the selected filters.' => 'Nenhum dado de navegador disponível para os filtros selecionados.',
    'No hourly data available for the selected filters.' => 'Nenhum dado por hora disponível para os filtros selecionados.',
    'Peak usage at' => 'Pico de uso às',

    // =========================================================================
    // Interaction Types
    // =========================================================================

    'Direct' => 'Direto',
    'Direct Visits' => 'Visitas diretas',
    'QR' => 'QR',
    'QR Scans' => 'Leituras de QR',
    'Button' => 'Botão',
    'Landing' => 'Destino',

    // =========================================================================
    // Analytics Export — CSV/Excel Column Headers
    // =========================================================================

    'Date/Time' => 'Data/Hora',
    'Status' => 'Status',
    'Smart Link URL' => 'URL do Smart Link',
    'Referrer' => 'Referrer',
    'Device Type' => 'Tipo de dispositivo',
    'Device Brand' => 'Marca do dispositivo',
    'Device Model' => 'Modelo do dispositivo',
    'OS Version' => 'Versão do SO',
    'Browser Version' => 'Versão do navegador',
    'Language' => 'Idioma',
    'User Agent' => 'User Agent',

    // =========================================================================
    // Time Periods
    // =========================================================================

    'Today' => 'Hoje',
    'Yesterday' => 'Ontem',
    'Last 7 days' => 'Últimos 7 dias',
    'Last 30 days' => 'Últimos 30 dias',
    'Last 90 days' => 'Últimos 90 dias',
    'All time' => 'Todo o período',
    'Date Range' => 'Intervalo de datas',

    // =========================================================================
    // Utilities
    // =========================================================================

    'Monitor link performance, track analytics, and manage cache for your {singularName} redirects and QR codes.' => 'Monitore o desempenho dos links, acompanhe as análises e gerencie o cache para seus redirecionamentos {singularName} e QR Codes.',
    'Active {pluginName}' => '{pluginName} ativos',
    'Links Status' => 'Status dos links',
    'Total {pluginName}' => 'Total de {pluginName}',
    'Performance' => 'Desempenho',
    'Total interactions tracked' => 'Total de interações rastreadas',
    'Redirects' => 'Redirecionamentos',
    'QR Codes' => 'QR Codes',
    'Devices' => 'Dispositivos',
    'Cache Status' => 'Status do cache',
    'Total cached entries' => 'Total de entradas em cache',
    'Active' => 'Ativo',
    'Pending' => 'Pendente',
    'Expired' => 'Expirado',
    'Disabled' => 'Desabilitado',
    'Navigation' => 'Navegação',
    'Access main plugin sections' => 'Acessar as seções principais do plugin',
    'Manage {pluginName}' => 'Gerenciar {pluginName}',
    'View Settings' => 'Ver configurações',
    'Cache Management' => 'Gerenciamento de cache',
    'Clear cached data to force regeneration. Useful after changing QR code settings or when troubleshooting.' => 'Limpe os dados em cache para forçar a regeneração. Útil após alterar as configurações de QR Code ou ao solucionar problemas.',
    'Clear QR Cache' => 'Limpar cache QR',
    'Clear Device Cache' => 'Limpar cache de dispositivos',
    'Clear All Caches' => 'Limpar todos os caches',
    'Analytics Data Management' => 'Gerenciamento de dados analíticos',
    'Permanently delete all analytics tracking data. This action cannot be undone!' => 'Exclua permanentemente todos os dados de rastreamento analítico. Esta ação não pode ser desfeita!',
    'Clear All Analytics' => 'Limpar todas as análises',
    'Are you sure you want to permanently delete ALL analytics data? This action cannot be undone!' => 'Você tem certeza de que deseja excluir permanentemente TODOS os dados analíticos? Esta ação não pode ser desfeita!',
    'This will delete all click tracking data and reset all click counts. Are you absolutely sure?' => 'Isso excluirá todos os dados de rastreamento de cliques e redefinirá todas as contagens de cliques. Você tem absoluta certeza?',
    'Failed to clear QR cache' => 'Falha ao limpar o cache QR',
    'Failed to clear device cache' => 'Falha ao limpar o cache de dispositivos',
    'Failed to clear caches' => 'Falha ao limpar os caches',
    'Failed to clear analytics' => 'Falha ao limpar as análises',

    // =========================================================================
    // Widgets — Analytics Summary
    // =========================================================================

    '{pluginName} - Analytics' => '{pluginName} – Análises',
    'Top Performer' => 'Melhor desempenho',
    'interactions' => 'interações',
    'View full analytics' => 'Ver análises completas',
    'You don\'t have permission to view analytics.' => 'Você não tem permissão para ver as análises.',
    'Analytics are disabled in plugin settings.' => 'As análises estão desabilitadas nas configurações do plugin.',

    // =========================================================================
    // Widgets — Top Links
    // =========================================================================

    '{pluginName} - Top Links' => '{pluginName} – Melhores links',
    'Link' => 'Link',
    'Number of Links' => 'Número de links',
    'How many top links to display (1-20)' => 'Quantos melhores links exibir (1–20)',
    'View all {pluginName}' => 'Ver todos os {pluginName}',
    'No {pluginName} yet' => 'Ainda não há {pluginName}',
    'Create your first {singularName} to see it here.' => 'Crie seu primeiro {singularName} para vê-lo aqui.',

    // =========================================================================
    // Public Templates — Redirect Page (redirect.twig)
    // =========================================================================

    'App Store' => 'App Store',
    'Google Play' => 'Google Play',
    'AppGallery' => 'AppGallery',
    'Amazon' => 'Amazon',
    'Windows Store' => 'Windows Store',
    'Mac App Store' => 'Mac App Store',
    'Continue to Website' => 'Continuar para o site',

    // =========================================================================
    // Public Templates — QR Code Page (qr.twig)
    // =========================================================================

    'Scan with your phone\'s camera to download' => 'Escaneie com a câmera do seu celular para baixar',

    // =========================================================================
    // Controller Messages — Flash Notices & Errors
    // =========================================================================

    // SmartlinksController
    'Smart link saved.' => 'Smart link salvo.',
    'Couldn\'t save smart link.' => 'Não foi possível salvar o smart link.',
    'Error saving smart link: {error}' => 'Erro ao salvar o smart link: {error}',
    'Could not save smart link.' => 'Não foi possível salvar o smart link.',
    'Smart link deleted.' => 'Smart link excluído.',
    'Couldn\'t delete smart link.' => 'Não foi possível excluir o smart link.',
    'Smart link restored.' => 'Smart link restaurado.',
    'Couldn\'t restore smart link.' => 'Não foi possível restaurar o smart link.',
    'Smart link permanently deleted.' => 'Smart link excluído permanentemente.',
    'Couldn\'t delete smart link permanently.' => 'Não foi possível excluir o smart link permanentemente.',
    'Smart link not found' => 'Smart link não encontrado',
    'Cannot edit trashed smart links.' => 'Não é possível editar smart links na lixeira.',
    'Failed to generate QR code.' => 'Falha ao gerar o QR Code.',
    // SettingsController
    'Settings saved.' => 'Configurações salvas.',
    'Couldn\'t save settings.' => 'Não foi possível salvar as configurações.',
    'Field layout saved.' => 'Layout de campos salvo.',
    'Couldn\'t save field layout.' => 'Não foi possível salvar o layout de campos.',
    'Analytics cleanup job has been queued. It will run in the background.' => 'A tarefa de limpeza de análises foi enfileirada. Ela será executada em segundo plano.',
    'QR code cache cleared successfully.' => 'Cache de QR Code limpo com sucesso.',
    'Cleared {count} QR code caches.' => '{count} caches de QR Code limpos.',
    'Device cache cleared successfully.' => 'Cache de dispositivos limpo com sucesso.',
    'Cleared {count} device detection caches.' => '{count} caches de detecção de dispositivos limpos.',
    'All caches cleared successfully.' => 'Todos os caches limpos com sucesso.',
    'Cleared {count} cache entries.' => '{count} entradas de cache limpas.',
    'Cleared {count} analytics records and reset all click counts.' => '{count} registros analíticos excluídos e todas as contagens de cliques redefinidas.',
    'An unexpected error occurred.' => 'Ocorreu um erro inesperado.',
    // AnalyticsController
    'No analytics data to export.' => 'Nenhum dado analítico para exportar.',
    // JS notices
    'Enter custom size (100-4096 pixels):' => 'Insira um tamanho personalizado (100–4096 pixels):',
    'Please enter a valid size between 100 and 4096 pixels' => 'Insira um tamanho válido entre 100 e 4096 pixels',
    'Reset QR code settings to plugin defaults?' => 'Redefinir as configurações do QR Code para os padrões do plugin?',

    // =========================================================================
    // Job Messages
    // =========================================================================

    '{pluginName}: Cleaning up old analytics' => '{pluginName}: Limpando análises antigas',
    'Deleting {count} old analytics records' => 'Excluindo {count} registros analíticos antigos',
    'Deleted {deleted} of {total} records' => '{deleted} de {total} registros excluídos',

    // =========================================================================
    // Validation Messages
    // =========================================================================

    'Only letters, numbers, hyphens, and underscores are allowed.' => 'Apenas letras, números, hífens e sublinhados são permitidos.',
    'Only letters, numbers, hyphens, underscores, and slashes are allowed.' => 'Apenas letras, números, hífens, sublinhados e barras são permitidos.',
    'Only lowercase letters, numbers, and underscores are allowed.' => 'Apenas letras minúsculas, números e sublinhados são permitidos.',
    '{attribute} should only contain letters, numbers, underscores, and hyphens.' => '{attribute} deve conter apenas letras, números, sublinhados e hífens.',
    'Slug prefix "{prefix}" conflicts with: {conflicts}. Suggestions: {suggestions}' => 'O prefixo slug "{prefix}" está em conflito com: {conflicts}. Sugestões: {suggestions}',
    'QR prefix cannot be the same as your slug prefix. Try: qr, code, qrc, or {slug}/qr' => 'O prefixo QR não pode ser igual ao prefixo slug. Tente: qr, code, qrc ou {slug}/qr',
    'Nested QR prefix must start with your slug prefix "{slug}". Use: {slug}/{qr} or use standalone like "qr"' => 'O prefixo QR aninhado deve começar com o prefixo slug "{slug}". Use: {slug}/{qr} ou use um prefixo independente como "qr"',
    'QR prefix "{prefix}" conflicts with: {conflicts}. Suggestions: {suggestions}' => 'O prefixo QR "{prefix}" está em conflito com: {conflicts}. Sugestões: {suggestions}',
    'Smart link base URL must start with http:// or https://' => 'A URL base do smart link deve começar com http:// ou https://',
    'Smart link base URL cannot contain spaces.' => 'A URL base do smart link não pode conter espaços.',
    'Unsupported token in smart link base URL. Supported tokens: {siteHandle}, {siteId}, {siteUid}.' => 'Token não suportado na URL base do smart link. Tokens suportados: {siteHandle}, {siteId}, {siteUid}.',

    // =========================================================================
    // Config Override Warnings
    // =========================================================================

    'This is being overridden by the <code>pluginName</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>pluginName</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableAnalytics</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>enableAnalytics</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>analyticsRetention</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>analyticsRetention</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>includeDisabledInExport</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>includeDisabledInExport</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>includeExpiredInExport</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>includeExpiredInExport</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrSize</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>defaultQrSize</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrColor</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>defaultQrColor</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrBgColor</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>defaultQrBgColor</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrFormat</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>defaultQrFormat</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrCodeCacheDuration</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>qrCodeCacheDuration</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrErrorCorrection</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>defaultQrErrorCorrection</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrMargin</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>defaultQrMargin</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrModuleStyle</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>qrModuleStyle</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrEyeStyle</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>qrEyeStyle</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrEyeColor</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>qrEyeColor</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableQrLogo</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>enableQrLogo</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrLogoVolumeUid</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>qrLogoVolumeUid</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>imageVolumeUid</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>imageVolumeUid</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrLogoSize</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>qrLogoSize</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableQrDownload</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>enableQrDownload</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrDownloadFilename</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>qrDownloadFilename</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>redirectTemplate</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>redirectTemplate</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrTemplate</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>qrTemplate</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableGeoDetection</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>enableGeoDetection</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>cacheDeviceDetection</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>cacheDeviceDetection</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>deviceDetectionCacheDuration</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>deviceDetectionCacheDuration</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>languageDetectionMethod</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>languageDetectionMethod</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>itemsPerPage</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>itemsPerPage</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>notFoundRedirectUrl</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>notFoundRedirectUrl</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enabledSites</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>enabledSites</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enabledIntegrations</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>enabledIntegrations</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>seomaticTrackingEvents</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>seomaticTrackingEvents</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>seomaticEventPrefix</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>seomaticEventPrefix</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>cacheStorageMethod</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>cacheStorageMethod</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableQrCodeCache</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>enableQrCodeCache</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>anonymizeIpAddress</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>anonymizeIpAddress</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>redirectManagerEvents</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>redirectManagerEvents</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>logLevel</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>logLevel</code> em <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>smartlinkBaseUrl</code> setting in <code>config/smartlink-manager.php</code>.' => 'Esta configuração está sendo sobrescrita pela configuração <code>smartlinkBaseUrl</code> em <code>config/smartlink-manager.php</code>.',

    // =========================================================================
    // General Interface
    // =========================================================================

    'Save Settings' => 'Salvar configurações',
    'Actions' => 'Ações',
    'Loading...' => 'Carregando...',
    'Error' => 'Erro',

    // =========================================================================
    // Behavior Settings — Select Options
    // =========================================================================

    'Browser preference' => 'Preferência do navegador',
    'IP geolocation' => 'Geolocalização IP',
    'Both' => 'Ambos',

    // =========================================================================
    // General Settings — URL Tips (Redirect Manager integration)
    // =========================================================================

    'Changing will break existing URLs. To migrate, create wildcard redirect in {redirectPluginName}: Source \'/old/*\' → Destination \'/new/$1\' (Match Type: Wildcard)' => 'A alteração quebrará as URLs existentes. Para migrar, crie um redirecionamento curinga em {redirectPluginName}: Fonte \'/old/*\' → Destino \'/new/$1\' (Tipo de correspondência: Curinga)',
    'Changing will break existing QR URLs. To migrate, create wildcard redirect in {redirectPluginName}: Source \'/old/*\' → Destination \'/new/$1\' (Match Type: Wildcard). Supports standalone (e.g., \'qr\') or nested (e.g., \'go/qr\') patterns.' => 'A alteração quebrará as URLs de QR existentes. Para migrar, crie um redirecionamento curinga em {redirectPluginName}: Fonte \'/old/*\' → Destino \'/new/$1\' (Tipo de correspondência: Curinga). Suporta padrões independentes (ex.: \'qr\') ou aninhados (ex.: \'go/qr\').',
    'Supports standalone (e.g., \'qr\') or nested (e.g., \'go/qr\') patterns. Checked for conflicts with ShortLink Manager.' => 'Suporta padrões independentes (ex.: \'qr\') ou aninhados (ex.: \'go/qr\'). Verificado para conflitos com ShortLink Manager.',

    // =========================================================================
    // QR Code Settings — Select Options
    // =========================================================================

    'Square' => 'Quadrado',
    'Rounded' => 'Arredondado',
    'Dots' => 'Pontos',
    'Leaf' => 'Folha',
    'Low (~7% correction)' => 'Baixo (~7% de correção)',
    'Medium (~15% correction)' => 'Médio (~15% de correção)',
    'Quartile (~25% correction)' => 'Quartil (~25% de correção)',
    'High (~30% correction)' => 'Alto (~30% de correção)',
    'Failed to generate preview' => 'Falha ao gerar a pré-visualização',

    // =========================================================================
    // Smart Link Fields — Image Size Options
    // =========================================================================

    'Extra Large' => 'Extra grande',
    'Large' => 'Grande',
    'Medium' => 'Médio',
    'Small' => 'Pequeno',

    // =========================================================================
    // Smart Link Field Input — Tooltip
    // =========================================================================

    'Clicks:' => 'Cliques:',

    // =========================================================================
    // Cache Settings — Info Boxes & Durations
    // =========================================================================

    'Cache Location' => 'Localização do cache',
    'Using Craft\'s configured Redis cache from <code>config/app.php</code>' => 'Usando o cache Redis configurado pelo Craft em <code>config/app.php</code>',
    'Redis Not Configured' => 'Redis não configurado',
    'To use Redis caching, install <code>yiisoft/yii2-redis</code> and configure it in <code>config/app.php</code>.' => 'Para usar o cache Redis, instale <code>yiisoft/yii2-redis</code> e configure-o em <code>config/app.php</code>.',
    'How it works' => 'Como funciona',
    'Device detection parses user-agent strings to identify devices, browsers, and operating systems' => 'A detecção de dispositivos analisa as strings user-agent para identificar dispositivos, navegadores e sistemas operacionais',
    'Results are cached to avoid re-parsing the same user-agent repeatedly' => 'Os resultados são armazenados em cache para evitar a análise repetida do mesmo user-agent',
    'Recommended to keep enabled for production sites' => 'Recomendado manter habilitado para sites em produção',
    'Cache duration in seconds. Current:' => 'Duração do cache em segundos. Atual:',

    // =========================================================================
    // Time Unit Strings (for JS secondsToHuman)
    // =========================================================================

    '{count} second' => '{count} segundo',
    '{count} seconds' => '{count} segundos',
    '{count} minute' => '{count} minuto',
    '{count} minutes' => '{count} minutos',
    '{count} hour' => '{count} hora',
    '{count} hours' => '{count} horas',
    '{count} day' => '{count} dia',
    '{count} days' => '{count} dias',

    // =========================================================================
    // Template Settings — Copy hints
    // =========================================================================

    'Required: copy <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/redirect.twig</code> to <code>templates/smartlink-manager/redirect.twig</code>' => 'Obrigatório: copie <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/redirect.twig</code> para <code>templates/smartlink-manager/redirect.twig</code>',
    'Required: copy <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/qr.twig</code> to <code>templates/smartlink-manager/qr.twig</code>' => 'Obrigatório: copie <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/qr.twig</code> para <code>templates/smartlink-manager/qr.twig</code>',

    // =========================================================================
    // Import/Export
    // =========================================================================

    'Manage import/export' => 'Gerenciar importar/exportar',
    'Import links' => 'Importar links',
    'Export links' => 'Exportar links',
    'Clear import history' => 'Limpar histórico de importação',
    'Export Smart Links' => 'Exportar Smart Links',
    'Export All Smart Links as CSV' => 'Exportar todos os Smart Links como CSV',
    'Import Smart Links' => 'Importar Smart Links',
    'You do not have permission to export smart links.' => 'Você não tem permissão para exportar smart links.',
    'You do not have permission to import smart links.' => 'Você não tem permissão para importar smart links.',
    'Download all your current smart links as a CSV file for backup or migration to another site.' => 'Baixe todos os seus smart links atuais como um arquivo CSV para backup ou migração para outro site.',
    'Import smart links from CSV. You\'ll map columns and preview before importing.' => 'Importe smart links do CSV. Você mapeará as colunas e visualizará antes de importar.',
    'Select a CSV file to import smart links' => 'Selecione um arquivo CSV para importar smart links',
    'No smart links to export.' => 'Nenhum smart link para exportar.',
    'Map your CSV columns to smart link fields. Required fields must be mapped.' => 'Mapeie suas colunas CSV para os campos de smart link. Os campos obrigatórios devem ser mapeados.',
    'Valid Smart Links to Import' => 'Smart Links válidos para importar',
    'No valid smart links found to import.' => 'Nenhum smart link válido encontrado para importar.',
    'Import {count} Smart Links' => 'Importar {count} Smart Links',
    'No Valid Smart Links to Import' => 'Nenhum Smart Link válido para importar',
    'Click the button below to import {count} valid smart link(s).' => 'Clique no botão abaixo para importar {count} smart link(s) válido(s).',
    'Import completed: {imported} smart links imported.' => 'Importação concluída: {imported} smart links importados.',
    'Import completed: {imported} imported, {failed} failed.' => 'Importação concluída: {imported} importados, {failed} com falha.',
    'Import completed: {imported} {pluginName} imported.' => 'Importação concluída: {imported} {pluginName} importados.',
    'Import completed: {imported} {pluginName} imported, {failed} failed.' => 'Importação concluída: {imported} {pluginName} importados, {failed} com falha.',
    'Failed to clear import history.' => 'Falha ao limpar o histórico de importação.',
    'Slug must be mapped.' => 'O slug deve ser mapeado.',
    'Slug (required)' => 'Slug (obrigatório)',
    'Fallback URL (required)' => 'URL alternativa (obrigatória)',
    'Image Asset ID' => 'ID do recurso de imagem',
    'Image Size (xl/lg/md/sm)' => 'Tamanho da imagem (xl/lg/md/sm)',
    'QR Enabled (1/0)' => 'QR habilitado (1/0)',
    'QR Size' => 'Tamanho do QR',
    'QR Color (#RRGGBB)' => 'Cor do QR (#RRGGBB)',
    'QR Background (#RRGGBB)' => 'Fundo do QR (#RRGGBB)',
    'QR Eye Color (#RRGGBB)' => 'Cor dos marcadores do QR (#RRGGBB)',
    'QR Format (png/svg)' => 'Formato do QR (png/svg)',
    'QR Logo Asset ID' => 'ID do recurso de logotipo do QR',
    'Hide Title (1/0)' => 'Ocultar título (1/0)',
    'Language Detection (1/0)' => 'Detecção de idioma (1/0)',
    'Metadata (JSON)' => 'Metadados (JSON)',

    // Import/Export — Controller messages
    'Unknown' => 'Desconhecido',
    'Please select a CSV file to upload.' => 'Selecione um arquivo CSV para fazer o upload.',
    'Failed to parse CSV: {error}' => 'Falha ao analisar o CSV: {error}',
    'No import data found. Please upload a CSV file.' => 'Nenhum dado de importação encontrado. Faça o upload de um arquivo CSV.',
    'No preview data found. Please map columns first.' => 'Nenhum dado de pré-visualização encontrado. Mapeie as colunas primeiro.',
    'Import session expired. Please upload the file again.' => 'A sessão de importação expirou. Faça o upload do arquivo novamente.',

    // Import/Export — Template UI
    'Import History' => 'Histórico de importação',
    'CSV Format' => 'Formato CSV',
    'Required columns:' => 'Colunas obrigatórias:',
    'Optional columns:' => 'Colunas opcionais:',
    'Import from CSV' => 'Importar do CSV',
    'CSV File' => 'Arquivo CSV',
    'CSV Delimiter' => 'Delimitador CSV',
    'Character used to separate values in your CSV (auto-detect is default)' => 'Caractere usado para separar os valores no CSV (detecção automática é o padrão)',
    'Auto (detect)' => 'Auto (detectar)',
    'Comma (,)' => 'Vírgula (,)',
    'Semicolon (;)' => 'Ponto e vírgula (;)',
    'Tab' => 'Tabulação',
    'Pipe (|)' => 'Barra vertical (|)',
    'The maximum file size is {size} and the import is limited to {rows} rows per file.' => 'O tamanho máximo do arquivo é {size} e a importação é limitada a {rows} linhas por arquivo.',
    'Upload & Map Columns' => 'Fazer upload e mapear colunas',
    'Clear history' => 'Limpar histórico',
    'No import history yet.' => 'Ainda não há histórico de importação.',
    'Are you sure you want to clear all import logs? This action cannot be undone.' => 'Você tem certeza de que deseja limpar todos os registros de importação? Esta ação não pode ser desfeita.',
    'Failed to clear history.' => 'Falha ao limpar o histórico.',
    'Map CSV Columns' => 'Mapear colunas CSV',
    'Your CSV has {count} rows. Map each CSV column to a smart link field.' => 'Seu CSV tem {count} linhas. Mapeie cada coluna CSV para um campo de smart link.',
    'Preview of CSV Data' => 'Pré-visualização dos dados CSV',
    'Showing first 5 rows. {total} total rows will be imported.' => 'Mostrando as primeiras 5 linhas. {total} linhas no total serão importadas.',
    'Column Mapping' => 'Mapeamento de colunas',
    'Note: only columns mapped to a field will be imported.' => 'Nota: apenas colunas mapeadas para um campo serão importadas.',
    '-- Do not import --' => '-- Não importar --',
    'Enabled (1/0)' => 'Habilitado (1/0)',
    'Site ID' => 'ID do site',
    'Site Handle' => 'Handle do site',
    'Track Analytics (1/0)' => 'Rastrear análises (1/0)',
    'Post Date (YYYY-MM-DD HH:MM:SS)' => 'Data de publicação (AAAA-MM-DD HH:MM:SS)',
    'Date Expired (YYYY-MM-DD HH:MM:SS)' => 'Data de expiração (AAAA-MM-DD HH:MM:SS)',
    'CSV Column' => 'Coluna CSV',
    'Maps to Field' => 'Mapeada para o campo',
    'Sample Data' => 'Dados de amostra',
    'Map Columns' => 'Mapear colunas',
    'Cancel' => 'Cancelar',
    'Preview Import' => 'Pré-visualizar importação',
    'Import Preview' => 'Pré-visualização da importação',
    'Total Rows' => 'Total de linhas',
    'Valid' => 'Válido',
    'Duplicates' => 'Duplicatas',
    'Errors' => 'Erros',
    'Duplicates (will be skipped)' => 'Duplicatas (serão ignoradas)',
    'Invalid Rows (will be skipped)' => 'Linhas inválidas (serão ignoradas)',
    'Row' => 'Linha',
    'Reason' => 'Motivo',
    'Image ID' => 'ID da imagem',
    'Ready to Import' => 'Pronto para importar',

    // Base partial: import-history
    'Created By' => 'Criado por',
    'Filename' => 'Nome do arquivo',
    'Imported' => 'Importado',
    'Failed' => 'Com falha',

    // Analytics partial
    'Device Breakdown' => 'Divisão por dispositivo',

];
