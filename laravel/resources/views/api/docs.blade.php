<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AutoAds Network · API Reference</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=JetBrains+Mono:wght@400;500&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
      body{margin:0;background:#0B0F17;font-family:'Plus Jakarta Sans',sans-serif}
      .topbar-custom{display:flex;align-items:center;gap:.75rem;padding:1rem 1.5rem;background:#070A10;border-bottom:1px solid rgba(255,255,255,.1);position:sticky;top:0;z-index:50}
      .topbar-custom .logo{height:2rem;width:2rem;display:inline-flex;align-items:center;justify-content:center;border-radius:.4rem;background:#F59E0B;color:#0B0F17;font-weight:800}
      .topbar-custom .brand{font-family:'Barlow Condensed',sans-serif;font-size:1.35rem;font-weight:800;letter-spacing:.03em;color:#fff}
      .topbar-custom .brand span{color:#F59E0B}
      .topbar-custom .tag{margin-left:auto;font-family:'JetBrains Mono',monospace;font-size:.7rem;color:#9CA3AF;text-transform:uppercase;letter-spacing:.1em}
      /* Dark-tune Swagger UI */
      .swagger-ui, .swagger-ui .info .title, .swagger-ui .opblock-tag, .swagger-ui .opblock .opblock-summary-operation-id, .swagger-ui table thead tr td, .swagger-ui table thead tr th, .swagger-ui .parameter__name, .swagger-ui .response-col_status, .swagger-ui label, .swagger-ui .model, .swagger-ui .model-title{color:#E5E7EB}
      .swagger-ui .info .base-url, .swagger-ui .info li, .swagger-ui .info p, .swagger-ui .info table, .swagger-ui .markdown p, .swagger-ui .opblock-description-wrapper p{color:#9CA3AF}
      .swagger-ui .scheme-container{background:#111827;box-shadow:none;border-bottom:1px solid rgba(255,255,255,.08)}
      .swagger-ui .opblock-tag{border-bottom:1px solid rgba(255,255,255,.08)}
      .swagger-ui section.models{border-color:rgba(255,255,255,.1)}
      .swagger-ui .btn.authorize{background:#F59E0B;border-color:#F59E0B;color:#0B0F17}
      .swagger-ui .btn.authorize svg{fill:#0B0F17}
    </style>
</head>
<body>
    <div class="topbar-custom">
      <span class="logo">A</span>
      <span class="brand">AUTOADS<span>·</span>NET API</span>
      <span class="tag">OpenAPI 3.0 · v1</span>
    </div>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-standalone-preset.js"></script>
    <script>
      window.onload = () => {
        window.ui = SwaggerUIBundle({
          url: @json($specUrl),
          dom_id: '#swagger-ui',
          deepLinking: true,
          persistAuthorization: true,
          presets: [SwaggerUIBundle.presets.apis, SwaggerUIStandalonePreset],
          layout: 'StandaloneLayout',
          tryItOutEnabled: true,
        });
      };
    </script>
</body>
</html>
