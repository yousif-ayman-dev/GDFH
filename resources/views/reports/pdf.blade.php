<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>تقرير تحليلات الإنتاجية — Tasker Enterprise</title>
  
  <style>
    :root {
      --color-navy: 13, 34, 58;
      --color-copper: 243, 132, 0;
      --color-blue: 43, 88, 168;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      background-color: #f8fafc;
      color: #0f172a;
      line-height: 1.5;
      padding: 24px;
    }

    .container {
      max-width: 900px;
      margin: 0 auto;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      padding: 32px;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 2px solid #f1f5f9;
      padding-bottom: 20px;
      margin-bottom: 24px;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .brand-logo {
      width: 40px;
      height: 40px;
      background-color: rgb(var(--color-copper));
      color: #ffffff;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 900;
      font-size: 20px;
    }

    .brand-title {
      font-size: 20px;
      font-weight: 800;
      color: rgb(var(--color-navy));
    }

    .brand-subtitle {
      font-size: 11px;
      color: rgb(var(--color-copper));
      font-weight: 600;
    }

    .meta-box {
      background-color: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 16px;
      margin-bottom: 24px;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 12px;
      font-size: 12px;
    }

    .meta-item span {
      color: #64748b;
      display: block;
      font-size: 11px;
      margin-bottom: 2px;
    }

    .meta-item strong {
      color: #0f172a;
      font-weight: 700;
    }

    .section-title {
      font-size: 15px;
      font-weight: 800;
      color: rgb(var(--color-navy));
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .section-title::before {
      content: '';
      width: 4px;
      height: 16px;
      background-color: rgb(var(--color-copper));
      border-radius: 2px;
    }

    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 12px;
      margin-bottom: 28px;
    }

    .kpi-card {
      background-color: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 14px;
      text-align: center;
    }

    .kpi-value {
      font-size: 22px;
      font-weight: 800;
      color: rgb(var(--color-blue));
    }

    .kpi-label {
      font-size: 11px;
      color: #64748b;
      margin-top: 2px;
      font-weight: 600;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 28px;
      font-size: 12px;
    }

    th {
      background-color: #f1f5f9;
      color: #334155;
      text-align: right;
      padding: 10px 14px;
      font-weight: 700;
      border-bottom: 1px solid #cbd5e1;
    }

    td {
      padding: 10px 14px;
      border-bottom: 1px solid #e2e8f0;
      color: #334155;
    }

    tr:nth-child(even) td {
      background-color: #f8fafc;
    }

    .badge {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 6px;
      font-size: 10px;
      font-weight: 700;
      background-color: #e2e8f0;
      color: #334155;
    }

    .badge-success {
      background-color: #dcfce7;
      color: #15803d;
    }

    .progress-bar-bg {
      width: 100%;
      height: 6px;
      background-color: #e2e8f0;
      border-radius: 3px;
      overflow: hidden;
      margin-top: 4px;
    }

    .progress-bar-fill {
      height: 100%;
      background-color: rgb(var(--color-blue));
      border-radius: 3px;
    }

    .actions {
      display: flex;
      justify-content: flex-end;
      gap: 12px;
      margin-bottom: 20px;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 16px;
      border-radius: 8px;
      font-size: 12px;
      font-weight: 700;
      cursor: pointer;
      border: 1px solid #cbd5e1;
      background-color: #ffffff;
      color: #334155;
      text-decoration: none;
    }

    .btn-primary {
      background-color: rgb(var(--color-navy));
      color: #ffffff;
      border-color: rgb(var(--color-navy));
    }

    @media print {
      body {
        background: none;
        padding: 0;
      }
      .container {
        border: none;
        box-shadow: none;
        padding: 0;
        max-width: 100%;
      }
      .actions, .no-print {
        display: none !important;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    
    {{-- Print Actions Header --}}
    <div class="actions no-print">
      <a href="{{ route('reports.index', $filters) }}" class="btn">
        العودة للتقارير
      </a>
      <button type="button" onclick="window.print()" class="btn btn-primary">
        🖨️ طباعة / حفظ كملف PDF
      </button>
    </div>

    {{-- Brand Header --}}
    <div class="header">
      <div class="brand">
        <x-application-logo class="h-10 w-auto" />
      </div>

      <div style="text-align: left;">
        <h1 style="font-size: 16px; font-weight: 800; color: rgb(var(--color-navy));">تقرير تحليلات الإنتاجية والأداء</h1>
        <p style="font-size: 11px; color: #64748b;">تاريخ التصدير: {{ $generated_at->format('Y-m-d H:i') }}</p>
      </div>
    </div>

    {{-- Meta Box --}}
    <div class="meta-box">
      <div class="meta-item">
        <span>المستخدم المستخرج:</span>
        <strong>{{ $user->name }} ({{ $user->email }})</strong>
      </div>
      <div class="meta-item">
        <span>فترة التقرير:</span>
        <strong>{{ !empty($filters['start_date']) ? $filters['start_date'] : 'البداية' }} &larr; {{ !empty($filters['end_date']) ? $filters['end_date'] : 'الآن' }}</strong>
      </div>
      <div class="meta-item">
        <span>المشروع المحدد:</span>
        <strong>
          @if (!empty($filters['project_id']) && $p = $user_projects->firstWhere('id', $filters['project_id']))
            {{ $p->title }}
          @else
            جميع المشاريع المتاحة
          @endif
        </strong>
      </div>
    </div>

    {{-- KPIs Grid --}}
    <div class="section-title">المؤشرات الرئيسية للأداء (KPIs)</div>
    <div class="kpi-grid">
      <div class="kpi-card">
        <div class="kpi-value">{{ $kpis['productivity_score'] }}%</div>
        <div class="kpi-label">مؤشر الإنتاجية العام</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-value">{{ $kpis['completion_rate'] }}%</div>
        <div class="kpi-label">معدل إنجاز المهام</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-value">{{ $kpis['total_projects'] }}</div>
        <div class="kpi-label">إجمالي المشاريع</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-value">{{ $kpis['total_tasks'] }}</div>
        <div class="kpi-label">إجمالي المهام</div>
      </div>
    </div>

    {{-- Projects Table --}}
    <div class="section-title">ملخص أداء المشاريع</div>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>اسم المشروع</th>
          <th>المالك</th>
          <th>الحالة</th>
          <th>نسبة الإنجاز</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($reports['projects'] as $project)
        <tr>
          <td>{{ $project->id }}</td>
          <td><strong>{{ $project->title }}</strong></td>
          <td>{{ $project->owner?->name ?? 'غير محدد' }}</td>
          <td>
            <span class="badge {{ $project->status === 'completed' ? 'badge-success' : '' }}">
              {{ $project->status }}
            </span>
          </td>
          <td style="width: 140px;">
            <div style="display: flex; justify-content: space-between; font-size: 10px; font-weight: 700;">
              <span>{{ $project->progress() }}%</span>
            </div>
            <div class="progress-bar-bg">
              <div class="progress-bar-fill" style="width: {{ $project->progress() }}%;"></div>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5" style="text-align: center; color: #64748b; padding: 20px;">لا توجد مشاريع مسجلة لهذه الفلاتر.</td>
        </tr>
        @endforelse
      </tbody>
    </table>

    {{-- Team Leaderboard Table --}}
    <div class="section-title">إنتاجية أعضاء الفريق</div>
    <table>
      <thead>
        <tr>
          <th>عضو الفريق</th>
          <th>المهام المكتملة</th>
          <th>إجمالي المهام المسندة</th>
          <th>معدل الإنجاز الشخصي</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($reports['user_leaderboard'] as $item)
        <tr>
          <td><strong>{{ $item['user']->name ?? 'مستخدم' }}</strong></td>
          <td>{{ $item['completed_tasks'] }}</td>
          <td>{{ $item['total_tasks'] }}</td>
          <td>
            <span class="badge badge-success">{{ $item['completion_rate'] }}%</span>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="4" style="text-align: center; color: #64748b; padding: 20px;">لا توجد بيانات إنتاجية مستخدمين للفلاتر المحددة.</td>
        </tr>
        @endforelse
      </tbody>
    </table>

    <div style="margin-top: 32px; border-t: 1px solid #e2e8f0; padding-top: 12px; display: flex; justify-content: space-between; font-size: 10px; color: #94a3b8;">
      <span>تم توليد هذا التقرير آلياً بواسطة نظام Tasker Enterprise</span>
      <span>الصفحة 1 من 1</span>
    </div>

  </div>

</body>
</html>
