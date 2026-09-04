<?php
require_once __DIR__ . '/components/render.php';
$manualYoutube = rtrim(brasasol_setting('social_youtube', 'https://www.youtube.com/@brasasoloficial'), '/') . '/videos';
$manualSetupVideo = brasasol_youtube_embed_url(brasasol_setting('manual_video_setup', 'https://youtu.be/4ZPLq2ZTwuA'));
$manualIgnitionVideo = brasasol_youtube_embed_url(brasasol_setting('manual_video_ignition', 'https://youtu.be/4ZPLq2ZTwuA'));
$manualCareVideo = brasasol_youtube_embed_url(brasasol_setting('manual_video_care', 'https://youtu.be/4ZPLq2ZTwuA'));
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php brasasol_render_seo(); ?>
        <title>Manual de uso | BRASASOL</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="icon" type="image/png" href="img/logo/brasasol-favicon.png">
<link rel="stylesheet" href="estilos/styles.css?v=brasasol-ux-v73-20260714">
    </head>

    <body class="bg-black text-white standard-page manual-page">
        <?php cargar_componente('topbar'); ?>
        <?php cargar_componente('navbar'); ?>

        <main>
            <section class="manual-hero" style="--manual-hero-image:url('<?= htmlspecialchars(brasasol_site_image('hero_manual','img/horno/top/top2.png'), ENT_QUOTES, 'UTF-8') ?>')">
                <div class="container">
                    <div class="manual-hero-copy">
                        <span class="manual-eyebrow">Guía oficial BRASASOL</span>
                        <h1>Bienvenido a la <span>experiencia</span> del sabor</h1>
                        <p>
                            Todo lo que necesitas para dominar tu cilindro asador: armado, encendido,
                            organización de alimentos, recetas base y cuidado correcto.
                        </p>
                    </div>
                </div>

                <div class="manual-hero-strip">
                    <div class="container manual-hero-strip-grid">
                        <span><i class="bi bi-layers"></i> Acero inoxidable</span>
                        <span><i class="bi bi-activity"></i> Cocción uniforme</span>
                        <span><i class="bi bi-shield-check"></i> Diseñado para durar</span>
                        <span><i class="bi bi-fire"></i> Sabor auténtico</span>
                    </div>
                </div>
            </section>

            <section id="antes-de-empezar" class="manual-step-section section-dark">
                <div class="container">
                    <div class="manual-step-title">
                        <span>1</span>
                        <h2>Antes de empezar</h2>
                    </div>

                    <div class="manual-section-label">Checklist de piezas</div>
                    <div class="manual-parts-grid">
                        <article class="manual-part-card">
                            <img src="img/horno/componentes/carbonera.png" alt="Carbonera BRASASOL">
                            <h3>Carbonera</h3>
                        </article>
                        <article class="manual-part-card">
                            <img src="img/horno/cilindro.png" alt="Haladores del cilindro BRASASOL">
                            <h3>Haladores</h3>
                        </article>
                        <article class="manual-part-card">
                            <img src="img/horno/componentes/ganchos.png" alt="Ganchos para cilindro BRASASOL">
                            <h3>Ganchos</h3>
                        </article>
                        <article class="manual-part-card">
                            <img src="img/horno/componentes/parrilla.png" alt="Parrilla para cilindro BRASASOL">
                            <h3>Parrilla</h3>
                        </article>
                        <article class="manual-part-card">
                            <img src="img/horno/componentes/termometro.png" alt="Termómetro para cilindro BRASASOL">
                            <h3>Termómetro</h3>
                        </article>
                    </div>

                    <div class="manual-video-block">
                        <span class="manual-video-label">Video sugerido</span>
                        <div class="manual-video-card"><iframe src="<?= htmlspecialchars($manualSetupVideo, ENT_QUOTES, 'UTF-8') ?>" title="Armado inicial del cilindro BRASASOL" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div>
                        <p>Cómo armar y preparar tu cilindro por primera vez.</p>
                    </div>
                </div>
            </section>

            <section id="encendido" class="manual-step-section">
                <div class="container">
                    <div class="manual-step-title">
                        <span>2</span>
                        <h2>Arte del encendido</h2>
                    </div>

                    <div class="manual-instruction-grid">
                        <article class="manual-instruction-card">
                            <img src="img/horno/componentes/carbonera.png" alt="Carbonera lista para encendido">
                            <div>
                                <strong>01</strong>
                                <h3>Carbonera</h3>
                                <p>Llena la carbonera sin exceder su capacidad. Con poco carbón, la temperatura baja a mitad del proceso.</p>
                            </div>
                        </article>
                        <article class="manual-instruction-card">
                            <img src="img/horno/promos/promo1.png" alt="Carbón recomendado para cilindro asador">
                            <div>
                                <strong>02</strong>
                                <h3>Carbón recomendado</h3>
                                <p>Usa carbón de buena calidad y trozos medianos o grandes para mantener calor parejo por más tiempo.</p>
                            </div>
                        </article>
                        <article class="manual-instruction-card">
                            <img src="img/horno/top/top2.png" alt="Control de aire en la tapa del cilindro">
                            <div>
                                <strong>03</strong>
                                <h3>Control del aire</h3>
                                <p>Regula los ductos de la tapa: abiertos para más calor, a media apertura para cocción estable y cerrados para apagado gradual.</p>
                            </div>
                        </article>
                    </div>

                    <div class="manual-tip">
                        <span>Tip pro</span>
                        <p>No uses líquidos inflamables que dejen olor o sabor. Una torre de carbón o pastillas de encendido son suficientes.</p>
                    </div>

                    <div class="manual-video-block">
                        <span class="manual-video-label">Video sugerido</span>
                        <div class="manual-video-card"><iframe src="<?= htmlspecialchars($manualIgnitionVideo, ENT_QUOTES, 'UTF-8') ?>" title="Encendido del cilindro BRASASOL" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div>
                        <p>La forma correcta de prender tu cilindro sin exceso de humo.</p>
                    </div>
                </div>
            </section>

            <section id="organizacion" class="manual-step-section section-dark">
                <div class="container">
                    <div class="manual-step-title">
                        <span>3</span>
                        <h2>Cómo organizar tus alimentos</h2>
                    </div>

                    <div class="manual-two-col">
                        <article class="manual-note-card">
                            <h3>Distribución de ganchos</h3>
                            <ul>
                                <li>Cuelga la carne evitando tocar la carbonera y las paredes.</li>
                                <li>Deja al menos 3 cm entre pieza y pieza para que el calor circule libre.</li>
                                <li>Las piezas más grandes van al centro porque reciben calor más uniforme.</li>
                                <li>Ninguna carne debe tocar las paredes durante la cocción.</li>
                            </ul>
                        </article>

                        <article class="manual-note-card">
                            <h3>Uso de la parrilla superior</h3>
                            <ul>
                                <li>Ideal para sartenes, vegetales, papas, panes y embutidos.</li>
                                <li>También sirve para cortes pequeños que no van en gancho.</li>
                                <li>La temperatura en parrilla es más suave que en zona de ganchos.</li>
                                <li>Voltea las piezas a mitad del tiempo para dorar parejo.</li>
                            </ul>
                        </article>
                    </div>

                    <div class="manual-heat-card">
                        <h3>Flujo de calor dentro del cilindro</h3>
                        <div class="manual-heat-diagram" aria-label="Diagrama de flujo de calor dentro del cilindro">
                            <svg viewBox="0 0 420 560" role="img">
                                <title>Flujo de calor BRASASOL</title>
                                <rect x="110" y="70" width="200" height="390" rx="22" fill="#0b0b0b" stroke="#F2B950" stroke-width="4"/>
                                <rect x="96" y="50" width="228" height="42" rx="10" fill="none" stroke="#F2B950" stroke-width="4"/>
                                <rect x="178" y="28" width="64" height="28" rx="10" fill="#9b5a1f" stroke="#F2B950" stroke-width="2"/>
                                <line x1="124" y1="142" x2="296" y2="142" stroke="#777" stroke-width="8" stroke-linecap="round"/>
                                <line x1="126" y1="102" x2="294" y2="102" stroke="#5a5a5a" stroke-width="6" stroke-linecap="round"/>
                                <g fill="#8f3d12">
                                    <ellipse cx="148" cy="190" rx="18" ry="36"/>
                                    <ellipse cx="202" cy="188" rx="18" ry="35"/>
                                    <ellipse cx="256" cy="190" rx="18" ry="36"/>
                                </g>
                                <g stroke="#bfbfbf" stroke-width="4" fill="none">
                                    <path d="M148 142v34"/>
                                    <path d="M202 142v32"/>
                                    <path d="M256 142v34"/>
                                </g>
                                <rect x="180" y="345" width="60" height="96" rx="8" fill="#202020" stroke="#555" stroke-width="3"/>
                                <g fill="#333">
                                    <circle cx="195" cy="365" r="5"/><circle cx="220" cy="365" r="5"/>
                                    <circle cx="195" cy="388" r="5"/><circle cx="220" cy="388" r="5"/>
                                    <circle cx="195" cy="412" r="5"/><circle cx="220" cy="412" r="5"/>
                                </g>
                                <path d="M132 320 C185 282 236 282 288 320" fill="none" stroke="#D96A29" stroke-width="4" marker-end="url(#arrowOrange)"/>
                                <path d="M288 338 C232 374 184 374 132 338" fill="none" stroke="#D96A29" stroke-width="4" marker-end="url(#arrowOrange)"/>
                                <path d="M118 438 V120" fill="none" stroke="#A61F2B" stroke-width="4" stroke-dasharray="8 8" marker-end="url(#arrowRed)"/>
                                <path d="M302 438 V120" fill="none" stroke="#A61F2B" stroke-width="4" stroke-dasharray="8 8" marker-end="url(#arrowRed)"/>
                                <path d="M142 486 V414" fill="none" stroke="#2f9cff" stroke-width="5" marker-end="url(#arrowBlue)"/>
                                <path d="M210 486 V414" fill="none" stroke="#2f9cff" stroke-width="5" marker-end="url(#arrowBlue)"/>
                                <path d="M278 486 V414" fill="none" stroke="#2f9cff" stroke-width="5" marker-end="url(#arrowBlue)"/>
                                <text x="210" y="24" text-anchor="middle" fill="#F2B950" font-size="14" font-weight="700">TAPA + VENTILACIÓN</text>
                                <text x="324" y="108" fill="#bfbfbf" font-size="12">PARRILLA</text>
                                <text x="324" y="124" fill="#bfbfbf" font-size="12">SUPERIOR</text>
                                <text x="22" y="424" fill="#bfbfbf" font-size="12">CARBONERA</text>
                                <text x="210" y="530" text-anchor="middle" fill="#2f9cff" font-size="12" font-weight="700">ORIFICIOS DE ENTRADA DE AIRE</text>
                                <defs>
                                    <marker id="arrowOrange" markerWidth="10" markerHeight="10" refX="8" refY="3" orient="auto"><path d="M0,0 L0,6 L9,3 z" fill="#D96A29"/></marker>
                                    <marker id="arrowRed" markerWidth="10" markerHeight="10" refX="8" refY="3" orient="auto"><path d="M0,0 L0,6 L9,3 z" fill="#A61F2B"/></marker>
                                    <marker id="arrowBlue" markerWidth="10" markerHeight="10" refX="8" refY="3" orient="auto"><path d="M0,0 L0,6 L9,3 z" fill="#2f9cff"/></marker>
                                </defs>
                            </svg>
                        </div>
                        <div class="manual-heat-legend">
                            <span><i class="heat-red"></i>Aire caliente asciende</span>
                            <span><i class="heat-orange"></i>Circulación de aire y humo</span>
                            <span><i class="heat-blue"></i>Aire fresco ingresa por la base</span>
                        </div>
                    </div>
                </div>
            </section>

            <section id="recetario" class="manual-step-section">
                <div class="container">
                    <div class="manual-step-title">
                        <span>4</span>
                        <h2>Recetario base para estrenar tu cilindro</h2>
                    </div>

                    <div class="manual-recipe-grid">
                        <article class="manual-recipe-card">
                            <img src="img/recetas/cerdo.png" alt="Cerdo al cilindro">
                            <div>
                                <h3>Cerdo al cilindro</h3>
                                <p><i class="bi bi-clock"></i> 2-3 horas aprox.</p>
                                <p><i class="bi bi-thermometer-half"></i> 180°C - 250°C</p>
                                <p><i class="bi bi-exclamation-lg"></i> Sazona, deja reposar de 2 a 4 horas y mantén las brasas sin llama directa.</p>
                            </div>
                        </article>
                        <article class="manual-recipe-card">
                            <img src="img/recetas/pollo.png" alt="Pollo al cilindro">
                            <div>
                                <h3>Pollo al cilindro</h3>
                                <p><i class="bi bi-clock"></i> 60-75 min.</p>
                                <p><i class="bi bi-thermometer-half"></i> 180°C - 250°C</p>
                                <p><i class="bi bi-exclamation-lg"></i> Marina al menos 4 horas y evita abrir constantemente la tapa para no perder calor.</p>
                            </div>
                        </article>
                        <article class="manual-recipe-card">
                            <img src="img/recetas/costillas.png" alt="Costillas al cilindro">
                            <div>
                                <h3>Costillas al cilindro</h3>
                                <p><i class="bi bi-clock"></i> 2-3 horas aprox.</p>
                                <p><i class="bi bi-thermometer-half"></i> 180°C - 250°C</p>
                                <p><i class="bi bi-exclamation-lg"></i> Gira a mitad de la cocción y humedece con jugo de limón para una textura crocante.</p>
                            </div>
                        </article>
                    </div>

                    <div class="text-center mt-4">
                        <a href="recetas.php" class="btn btn-warning btn-lg rounded-pill"><i class="bi bi-journal-richtext me-2"></i>Ver más recetas</a>
                    </div>
                </div>
            </section>

            <section id="cuidado" class="manual-step-section section-dark">
                <div class="container">
                    <div class="manual-step-title">
                        <span>5</span>
                        <h2>Cuidado y limpieza</h2>
                    </div>

                    <p class="manual-section-copy">
                        Un cilindro bien cuidado dura años. Estos pasos son clave para zonas húmedas y para conservar
                        el acabado del acero.
                    </p>

                    <div class="manual-care-grid">
                        <article>
                            <i class="bi bi-droplet-half"></i>
                            <h3>Lavado</h3>
                            <p>Espera que enfríe por completo. Retira residuos y lava con esponja suave.</p>
                        </article>
                        <article>
                            <i class="bi bi-wind"></i>
                            <h3>Secado</h3>
                            <p>Seca de inmediato con paño seco. No dejes agua acumulada en partes metálicas.</p>
                        </article>
                        <article>
                            <i class="bi bi-shield"></i>
                            <h3>Protección</h3>
                            <p>Aplica una capa fina de aceite vegetal en zonas internas si habrá humedad.</p>
                        </article>
                        <article>
                            <i class="bi bi-box-seam"></i>
                            <h3>Almacenamiento</h3>
                            <p>Guárdalo en un lugar seco y ventilado. Usa funda o protección si está expuesto.</p>
                        </article>
                    </div>

                    <div class="manual-video-block">
                        <span class="manual-video-label">Video sugerido</span>
                        <div class="manual-video-card"><iframe src="<?= htmlspecialchars($manualCareVideo, ENT_QUOTES, 'UTF-8') ?>" title="Cuidado del cilindro BRASASOL" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div>
                        <p>Mantenimiento profundo para que tu cilindro luzca siempre bien.</p>
                    </div>

                    <div class="manual-review-card">
                        <h3>Revisión periódica cada 3 meses</h3>
                        <ul>
                            <li>Revisa que los ganchos no tengan fisuras ni deformaciones por uso continuado.</li>
                            <li>Verifica que el termómetro marque correctamente usando agua hirviendo.</li>
                            <li>Comprueba que sellos y ductos de ventilación abran y cierren sin trabas.</li>
                            <li>Si aparece óxido superficial, lija con lana de acero fina y aplica aceite de inmediato.</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="py-5 bg-black manual-download-section">
                <div class="container">
                    <div class="manual-download-card">
                        <div class="manual-download-icon"><i class="bi bi-file-earmark-pdf"></i></div>
                        <div class="manual-download-copy">
                            <span class="product-kicker">Manual técnico oficial</span>
                            <h2>Ten todas las indicaciones a la mano</h2>
                            <p>Descarga el manual completo con condiciones de operación, componentes, encendido, preparación, montaje, seguridad, mantenimiento, control de calidad y recetas para el cilindro asador BRASASOL.</p>
                            <div class="manual-download-meta">
                                <span><i class="bi bi-filetype-pdf"></i> Formato PDF</span>
                                <span><i class="bi bi-journal-check"></i> 6 páginas</span>
                                <span><i class="bi bi-shield-check"></i> Documento oficial</span>
                            </div>
                        </div>
                        <a href="documentos/manual-cilindro-asador-brasasol.pdf" download class="btn btn-warning btn-lg rounded-pill manual-download-btn"><i class="bi bi-download me-2"></i>Descargar manual</a>
                    </div>
                </div>
            </section>
        </main>

        <?php cargar_componente('footer'); ?>
        <?php cargar_componente('cookies'); ?>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
