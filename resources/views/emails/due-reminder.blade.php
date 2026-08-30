<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembrete de Vencimento de Gastos</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 20px;
            color: #334155;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .header {
            background-color: #4f46e5;
            color: #ffffff;
            padding: 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .content {
            padding: 24px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 12px;
        }
        .intro-text {
            font-size: 14px;
            line-height: 1.5;
            color: #64748b;
            margin-bottom: 20px;
        }
        .item-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .item-details {
            flex: 1;
        }
        .item-title {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
        }
        .item-category {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }
        .item-due {
            font-size: 12px;
            color: #e11d48;
            font-weight: 600;
            margin-top: 2px;
        }
        .item-amount {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            text-align: right;
            margin-left: 12px;
        }
        .cta-container {
            text-align: center;
            margin: 28px 0 12px;
        }
        .cta-button {
            display: inline-block;
            background-color: #4f46e5;
            color: #ffffff !important;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 8px;
        }
        .footer {
            background-color: #f1f5f9;
            padding: 16px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Gestão Financeira Pessoal</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">Olá, {{ $user->name }}!</div>
            <div class="intro-text">
                Identificamos que você possui contas e despesas com vencimento próximo cadastradas no GFP. Confira a lista abaixo para manter suas finanças em dia:
            </div>

            <!-- Gastos Avulsos a Vencer -->
            @if ($expenses->isNotEmpty())
                <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">
                    🔹 Gastos Avulsos
                </div>
                @foreach ($expenses as $expense)
                    <div class="item-card">
                        <div class="item-details">
                            <div class="item-title">{{ $expense->description }}</div>
                            <div class="item-category">🏷️ {{ $expense->category->name }}</div>
                            <div class="item-due">📅 Vencimento: {{ $expense->date->format('d/m/Y') }} ({{ $expense->date->isToday() ? 'HOJE' : 'em ' . $expense->date->diffForHumans(null, true) }})</div>
                        </div>
                        <div class="item-amount">
                            {{ $expense->formatted_amount }}
                        </div>
                    </div>
                @endforeach
            @endif

            <!-- Ocorrências de Recorrentes a Vencer -->
            @if ($occurrences->isNotEmpty())
                <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin: 16px 0 8px; letter-spacing: 0.5px;">
                    🔄 Gastos Recorrentes
                </div>
                @foreach ($occurrences as $occ)
                    <div class="item-card">
                        <div class="item-details">
                            <div class="item-title">{{ $occ->description }}</div>
                            <div class="item-category">🏷️ {{ $occ->category->name }}</div>
                            <div class="item-due">📅 Vencimento: {{ $occ->due_date->format('d/m/Y') }} ({{ $occ->due_date->isToday() ? 'HOJE' : 'em ' . $occ->due_date->diffForHumans(null, true) }})</div>
                        </div>
                        <div class="item-amount">
                            {{ $occ->formatted_expected_amount }}
                        </div>
                    </div>
                @endforeach
            @endif

            <!-- Botão de Ação -->
            <div class="cta-container">
                <a href="{{ route('expenses.index') }}" class="cta-button">
                    Acessar Minhas Contas e Pagar
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0;">Você recebeu esta mensagem automática porque possui uma conta ativa no GFP.</p>
            <p style="margin: 4px 0 0;">© {{ date('Y') }} GFP — Sistema de Gestão Financeira Pessoal.</p>
        </div>
    </div>
</body>
</html>
