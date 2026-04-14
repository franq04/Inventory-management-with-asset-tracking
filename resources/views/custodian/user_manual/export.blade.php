<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $manualTitle }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #1f2937;
            background: #f9fafb;
            line-height: 1.6;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px 24px;
        }
        .hero {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 50%, #1e3a8a 100%);
            color: #fff;
            border-radius: 16px;
            padding: 48px 32px;
            margin-bottom: 40px;
            text-align: left;
            box-shadow: 0 20px 40px rgba(37, 99, 235, 0.15);
        }
        .hero h1 {
            font-size: 2.5em;
            font-weight: 900;
            margin-bottom: 12px;
            letter-spacing: -0.02em;
        }
        .hero p {
            font-size: 1.05em;
            opacity: 0.95;
            line-height: 1.6;
            max-width: 700px;
        }
        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 32px;
            margin-bottom: 32px;
            page-break-inside: avoid;
        }
        .card h2 {
            font-size: 1.75em;
            color: #1f2937;
            margin-bottom: 24px;
            font-weight: 700;
        }
        .toc-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }
        .toc-group {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: linear-gradient(135deg, #f0f9ff 0%, #f8fafc 100%);
            padding: 16px;
        }
        .toc-group h3 {
            font-size: 0.85em;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #2563eb;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .toc-group ol {
            margin: 0;
            padding-left: 20px;
            list-style-position: inside;
        }
        .toc-group li {
            margin: 8px 0;
            font-size: 0.95em;
            color: #374151;
        }
        .step {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #fcfdfe;
            padding: 24px;
            margin: 20px 0;
            page-break-inside: avoid;
        }
        .step small {
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #2563eb;
            font-weight: 700;
            font-size: 0.75em;
        }
        .step h3 {
            margin: 10px 0 16px;
            color: #1f2937;
            font-size: 1.5em;
            font-weight: 700;
        }
        .step p {
            margin: 0 0 16px;
            font-size: 0.95em;
            line-height: 1.65;
            color: #374151;
        }
        .screenshot-container {
            margin: 24px 0;
        }
        .step img {
            height: 520px;
            width: auto;
            display: block;
            margin: 0 auto;
            object-fit: contain;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        @media (max-width: 768px) {
            .hero {
                padding: 32px 20px;
            }
            .hero h1 {
                font-size: 1.75em;
            }
            .card {
                padding: 20px;
            }
            .toc-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media print {
            body {
                background: #fff;
            }
            .hero {
                box-shadow: none;
                page-break-after: avoid;
            }
            .card {
                border: none;
                box-shadow: none;
                padding: 0;
                margin: 0 0 32px 0;
            }
            .step {
                page-break-inside: avoid;
                border: none;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Hero Section -->
    <section class="hero">
        <h1>{{ $manualTitle }}</h1>
        <p>{{ $manualIntro }}</p>
    </section>

    <!-- Table of Contents -->
    <section class="card">
        <h2>Table of Contents</h2>
        <div class="toc-grid">
            @foreach($sections as $section)
                <div class="toc-group">
                    <h3>{{ $section['category'] }}</h3>
                    <ol>
                        @foreach($section['items'] as $step)
                            <li>{{ $step['number'] }}. {{ $step['title'] }}</li>
                        @endforeach
                    </ol>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Sections -->
    @foreach($sections as $section)
        <section class="card">
            <h2>{{ $section['category'] }}</h2>
            <p style="margin-bottom: 20px; color: #4b5563; font-size: 0.98em;">{{ $section['intro'] }}</p>
            @foreach($section['items'] as $step)
                <article class="step">
                    <small>Step {{ $step['number'] }}</small>
                    <h3>{{ $step['title'] }}</h3>
                    <p>{{ $step['description'] }}</p>
                    <div class="screenshot-container">
                        <img src="{{ $step['image_url'] }}" alt="{{ $step['title'] }} screenshot">
                    </div>
                </article>
            @endforeach
        </section>
    @endforeach
</div>
</body>
</html>
