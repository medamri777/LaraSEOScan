@extends('layouts.app')

@section('title', 'Detailed SEO Analyzer - Seo4ma')

@section('content')
<style>
    :root {
        --kick-green: #10b981;
        --kick-green-standard: #059669;
        --kick-purple: #7c3aed;
        --kick-live-red: #ef4444;
        --kick-bg-base: #f9fafb;
        --kick-bg-chat: #ffffff;
        --kick-surface-1: #ffffff;
        --kick-surface-2: #f9fafb;
        --kick-surface-3: #f3f4f6;
        --kick-text-primary: #111827;
        --kick-text-secondary: #6b7280;
        --kick-text-on-accent: #ffffff;
        --kick-border-muted: #d1d5db;
        --kick-border-subtle: #e5e7eb;
        --kick-radius-sm: 6px;
        --kick-radius-md: 8px;
        --kick-radius-lg: 12px;
        --kick-radius-pill: 9999px;
        --kick-radius-circle: 50%;
        --kick-font-primary: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .analyzer-wrapper {
        max-width: 1400px;
        margin: 0 auto;
        font-family: var(--kick-font-primary);
    }

    .analyzer-header {
        text-align: center;
        margin-bottom: 2.5rem;
        margin-top: 0.75rem;
    }

    .analyzer-header .badge-premium {
        background: var(--kick-green);
        color: var(--kick-text-on-accent);
        padding: 0.375rem 0.875rem;
        border-radius: var(--kick-radius-pill);
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        display: inline-block;
        margin-bottom: 0.875rem;
    }

    .analyzer-header h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--kick-text-primary);
        margin-bottom: 0.5rem;
        font-family: var(--kick-font-primary);
    }

    .analyzer-header p {
        color: var(--kick-text-secondary);
        max-width: 550px;
        margin: 0 auto 1.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
    }

    .search-container {
        max-width: 600px;
        margin: 0 auto;
    }

    .search-box {
        background: #f9fafb;
        border: 1px solid var(--kick-border-subtle);
        border-radius: var(--kick-radius-md);
        padding: 0.375rem;
        display: flex;
        align-items: center;
        transition: border-color 0.2s;
    }

    .search-box:focus-within {
        border-color: var(--kick-green);
    }

    .search-icon {
        padding: 0 0.75rem;
        color: var(--kick-text-secondary);
        font-size: 1.125rem;
    }

    .search-input {
        flex: 1;
        background: transparent;
        border: none;
        color: var(--kick-text-primary);
        padding: 0.625rem 0;
        font-size: 0.875rem;
        font-family: var(--kick-font-primary);
    }

    .search-input::placeholder {
        color: var(--kick-text-secondary);
    }

    .search-input:focus {
        outline: none;
    }

    .btn-analyze {
        background: var(--kick-green);
        border: none;
        color: var(--kick-text-on-accent);
        padding: 0.625rem 1.25rem;
        border-radius: var(--kick-radius-md);
        font-weight: 700;
        font-size: 0.8125rem;
        transition: filter 0.2s;
        white-space: nowrap;
        font-family: var(--kick-font-primary);
    }

    .btn-analyze:hover {
        filter: brightness(1.1);
        color: var(--kick-text-on-accent);
    }

    .btn-analyze:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .search-examples {
        margin-top: 0.625rem;
        text-align: center;
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        align-items: center;
    }

    .search-examples a {
        color: var(--kick-green);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.8125rem;
    }

    .search-examples a:hover {
        text-decoration: underline;
    }

    .search-examples span {
        color: var(--kick-text-secondary);
        font-size: 0.8125rem;
    }

    .loading-overlay {
        text-align: center;
        padding: 3rem 2rem;
    }

    .loading-spinner {
        width: 2.5rem;
        height: 2.5rem;
        border: 3px solid var(--kick-border-subtle);
        border-top-color: var(--kick-green);
        border-radius: var(--kick-radius-circle);
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .loading-overlay h4 {
        color: var(--kick-text-primary);
        font-weight: 700;
        margin-bottom: 0.375rem;
        font-size: 1rem;
    }

    .loading-overlay p {
        color: var(--kick-text-secondary);
        font-size: 0.8125rem;
    }

    .alert-kick {
        background: #ffffff;
        border: 1px solid var(--kick-border-subtle);
        border-radius: var(--kick-radius-md);
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: flex-start;
        gap: 0.875rem;
    }

    .alert-kick.alert-error {
        border-left: 3px solid var(--kick-live-red);
    }

    .alert-kick.alert-success {
        border-left: 3px solid var(--kick-green);
    }

    .alert-icon {
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .alert-error .alert-icon { color: var(--kick-live-red); }
    .alert-success .alert-icon { color: var(--kick-green); }

    .alert-content h4 {
        color: var(--kick-text-primary);
        font-weight: 700;
        margin-bottom: 0.125rem;
        font-size: 0.9375rem;
    }

    .alert-content p {
        color: var(--kick-text-secondary);
        margin: 0;
        font-size: 0.8125rem;
    }

    .alert-content a {
        color: var(--kick-green);
        text-decoration: none;
        font-weight: 600;
    }

    .alert-actions {
        margin-left: auto;
        display: flex;
        gap: 0.5rem;
    }

    .btn-kick-outline {
        background: transparent;
        border: 1px solid var(--kick-border-muted);
        color: var(--kick-text-primary);
        padding: 0.375rem 0.875rem;
        border-radius: var(--kick-radius-md);
        font-size: 0.75rem;
        font-weight: 600;
        transition: all 0.2s;
        font-family: var(--kick-font-primary);
    }

    .btn-kick-outline:hover {
        border-color: var(--kick-green);
        color: var(--kick-green);
    }

    .results-container {
        margin-top: 1.5rem;
    }

    .layout-grid {
        display: grid;
        grid-template-columns: 240px 1fr;
        gap: 1.25rem;
    }

    @media (max-width: 1024px) {
        .layout-grid {
            grid-template-columns: 1fr;
        }
    }

    .sidebar-card {
        background: #ffffff;
        border: 1px solid var(--kick-border-subtle);
        border-radius: var(--kick-radius-md);
        padding: 0.75rem;
        position: sticky;
        top: 1.5rem;
    }

    .sidebar-title {
        font-size: 0.6875rem;
        font-weight: 700;
        color: var(--kick-text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.5rem 0.75rem;
        margin-bottom: 0.25rem;
    }

    .nav-tabs-kick {
        display: flex;
        flex-direction: column;
        gap: 0.125rem;
    }

    .nav-tab-kick {
        background: transparent;
        border: none;
        color: var(--kick-text-secondary);
        padding: 0.625rem 0.75rem;
        border-radius: var(--kick-radius-sm);
        text-align: left;
        font-weight: 500;
        font-size: 0.8125rem;
        transition: all 0.15s;
        display: flex;
        align-items: center;
        gap: 0.625rem;
        cursor: pointer;
        font-family: var(--kick-font-primary);
    }

    .nav-tab-kick:hover {
        background: #f9fafb;
        color: var(--kick-text-primary);
    }

    .nav-tab-kick.active {
        color: var(--kick-text-primary);
        border-bottom: 2px solid var(--kick-green);
        border-radius: 0;
        background: transparent;
    }

    .nav-tab-kick i {
        font-size: 1rem;
    }

    .nav-tab-kick .badge-count {
        margin-left: auto;
        background: #f3f4f6;
        color: var(--kick-text-secondary);
        padding: 0.125rem 0.375rem;
        border-radius: var(--kick-radius-pill);
        font-size: 0.625rem;
        font-weight: 700;
    }

    .nav-tab-kick.active .badge-count {
        background: var(--kick-green);
        color: var(--kick-text-on-accent);
    }

    .nav-tab-kick .badge-danger {
        background: var(--kick-live-red);
        color: var(--kick-text-primary);
    }

    .content-area .tab-pane {
        display: none;
    }

    .content-area .tab-pane.active {
        display: block;
    }

    .content-card {
        background: #ffffff;
        border: 1px solid var(--kick-border-subtle);
        border-radius: var(--kick-radius-md);
        padding: 1.25rem;
        margin-bottom: 1.25rem;
    }

    .content-card h4 {
        color: var(--kick-text-primary);
        font-weight: 700;
        margin-bottom: 1.25rem;
        padding-bottom: 0.625rem;
        border-bottom: 1px solid var(--kick-border-subtle);
        font-size: 1rem;
    }

    .score-section {
        display: grid;
        grid-template-columns: 120px 1fr;
        gap: 1.5rem;
        align-items: center;
    }

    @media (max-width: 640px) {
        .score-section {
            grid-template-columns: 1fr;
            text-align: center;
        }
    }

    .score-ring {
        width: 120px;
        height: 120px;
        position: relative;
        margin: 0 auto;
    }

    .score-ring svg {
        transform: rotate(-90deg);
    }

    .score-ring circle {
        fill: transparent;
    }

    .score-ring .bg-ring {
        stroke: #f3f4f6;
        stroke-width: 10;
    }

    .score-ring .progress-ring {
        stroke-width: 10;
        stroke-linecap: round;
        stroke-dasharray: 314;
    }

    .score-value {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }

    .score-value h2 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        line-height: 1;
    }

    .score-value small {
        font-size: 0.625rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--kick-text-secondary);
    }

    .score-info .badge-premium {
        background: var(--kick-green);
        color: var(--kick-text-on-accent);
        padding: 0.25rem 0.625rem;
        border-radius: var(--kick-radius-pill);
        font-size: 0.625rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        display: inline-block;
        margin-bottom: 0.625rem;
    }

    .score-info h3 {
        color: var(--kick-text-primary);
        font-weight: 700;
        margin-bottom: 0.375rem;
        font-size: 1rem;
    }

    .score-info p {
        color: var(--kick-text-secondary);
        margin: 0;
        font-size: 0.8125rem;
        line-height: 1.5;
    }

    .severity-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 0.875rem;
        margin-bottom: 1.25rem;
    }

    .severity-card {
        background: #ffffff;
        border: 1px solid var(--kick-border-subtle);
        border-radius: var(--kick-radius-md);
        padding: 1rem;
        border-top: 3px solid;
    }

    .severity-card.errors { border-top-color: var(--kick-live-red); }
    .severity-card.warnings { border-top-color: var(--kick-live-red); }
    .severity-card.passed { border-top-color: var(--kick-green); }

    .severity-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.625rem;
    }

    .severity-header h6 {
        color: var(--kick-text-secondary);
        font-weight: 700;
        margin: 0;
        font-size: 0.75rem;
        text-transform: uppercase;
    }

    .severity-icon {
        width: 28px;
        height: 28px;
        border-radius: var(--kick-radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
    }

    .severity-card.errors .severity-icon {
        background: rgba(233, 25, 22, 0.15);
        color: var(--kick-live-red);
    }

    .severity-card.warnings .severity-icon {
        background: rgba(233, 25, 22, 0.15);
        color: var(--kick-live-red);
    }

    .severity-card.passed .severity-icon {
        background: rgba(83, 252, 24, 0.15);
        color: var(--kick-green);
    }

    .severity-count {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.125rem;
    }

    .severity-card.errors .severity-count { color: var(--kick-live-red); }
    .severity-card.warnings .severity-count { color: var(--kick-live-red); }
    .severity-card.passed .severity-count { color: var(--kick-green); }

    .severity-desc {
        font-size: 0.6875rem;
        color: var(--kick-text-secondary);
        line-height: 1.4;
    }

    .filter-bar {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 0.875rem;
        margin-bottom: 1.25rem;
        padding-bottom: 0.875rem;
        border-bottom: 1px solid var(--kick-border-subtle);
    }

    .filter-bar h5 {
        color: var(--kick-text-primary);
        font-weight: 700;
        margin: 0;
        font-size: 0.9375rem;
    }

    .filter-group {
        display: flex;
        gap: 0.375rem;
    }

    .btn-filter {
        background: #f9fafb;
        border: 1px solid var(--kick-border-subtle);
        color: var(--kick-text-secondary);
        padding: 0.3125rem 0.625rem;
        border-radius: var(--kick-radius-pill);
        font-size: 0.6875rem;
        font-weight: 600;
        transition: all 0.15s;
        cursor: pointer;
        font-family: var(--kick-font-primary);
    }

    .btn-filter:hover {
        background: #f3f4f6;
        color: var(--kick-text-primary);
    }

    .btn-filter.active {
        background: var(--kick-green);
        border-color: var(--kick-green);
        color: var(--kick-text-on-accent);
    }

    .diagnostic-item {
        background: #f9fafb;
        border: 1px solid var(--kick-border-subtle);
        border-radius: var(--kick-radius-md);
        padding: 1rem;
        margin-bottom: 0.875rem;
        border-left: 3px solid;
    }

    .diagnostic-item.error { border-left-color: var(--kick-live-red); }
    .diagnostic-item.warning { border-left-color: var(--kick-live-red); }
    .diagnostic-item.success { border-left-color: var(--kick-green); }

    .diagnostic-header {
        display: flex;
        flex-wrap: wrap;
        gap: 0.375rem;
        margin-bottom: 0.625rem;
    }

    .badge-severity {
        padding: 0.1875rem 0.5rem;
        border-radius: var(--kick-radius-pill);
        font-size: 0.625rem;
        font-weight: 700;
    }

    .badge-severity.error {
        background: var(--kick-live-red);
        color: var(--kick-text-primary);
    }

    .badge-severity.warning {
        background: #f3f4f6;
        color: var(--kick-text-secondary);
    }

    .badge-severity.success {
        background: var(--kick-green);
        color: var(--kick-text-on-accent);
    }

    .badge-category {
        background: #f3f4f6;
        color: var(--kick-text-secondary);
        padding: 0.1875rem 0.5rem;
        border-radius: var(--kick-radius-pill);
        font-size: 0.625rem;
        font-weight: 600;
    }

    .diagnostic-item h5 {
        color: var(--kick-text-primary);
        font-weight: 700;
        margin-bottom: 0.375rem;
        font-size: 0.9375rem;
    }

    .diagnostic-item p {
        color: var(--kick-text-secondary);
        font-size: 0.8125rem;
        margin-bottom: 0.625rem;
        line-height: 1.5;
    }

    .recommendation-box {
        background: #ffffff;
        border: 1px solid var(--kick-border-subtle);
        border-radius: var(--kick-radius-sm);
        padding: 0.875rem;
    }

    .recommendation-box .icon {
        color: var(--kick-green);
        margin-right: 0.625rem;
    }

    .recommendation-box strong {
        color: var(--kick-text-primary);
        font-size: 0.75rem;
        display: block;
        margin-bottom: 0.25rem;
        font-weight: 700;
    }

    .recommendation-box span {
        color: var(--kick-text-secondary);
        font-size: 0.8125rem;
        line-height: 1.5;
    }

    .metric-row {
        margin-bottom: 1.25rem;
    }

    .metric-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .metric-header label {
        color: var(--kick-text-secondary);
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        margin: 0;
    }

    .metric-value {
        padding: 0.1875rem 0.5rem;
        border-radius: var(--kick-radius-pill);
        font-size: 0.625rem;
        font-weight: 700;
    }

    .metric-value.optimal {
        background: var(--kick-green);
        color: var(--kick-text-on-accent);
    }

    .metric-value.short {
        background: #f3f4f6;
        color: var(--kick-text-secondary);
    }

    .metric-value.long, .metric-value.missing {
        background: var(--kick-live-red);
        color: var(--kick-text-primary);
    }

    .metric-display {
        background: #f9fafb;
        border: 1px solid var(--kick-border-subtle);
        border-radius: var(--kick-radius-sm);
        padding: 0.875rem;
    }

    .metric-display span {
        color: var(--kick-text-primary);
        font-size: 0.9375rem;
        font-weight: 500;
    }

    .metric-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 0.375rem;
        padding: 0 0.125rem;
    }

    .metric-footer small {
        color: var(--kick-text-secondary);
        font-size: 0.6875rem;
    }

    .metric-footer .progress-value {
        font-weight: 700;
        font-size: 0.6875rem;
        color: var(--kick-green);
    }

    .progress-bar-kick {
        height: 4px;
        background: #f9fafb;
        border-radius: var(--kick-radius-pill);
        margin-top: 0.375rem;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        border-radius: var(--kick-radius-pill);
        transition: width 0.3s;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 0.875rem;
    }

    .info-card {
        background: #f9fafb;
        border: 1px solid var(--kick-border-subtle);
        border-radius: var(--kick-radius-sm);
        padding: 0.875rem;
    }

    .info-card label {
        color: var(--kick-text-secondary);
        font-weight: 700;
        font-size: 0.625rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: block;
        margin-bottom: 0.375rem;
    }

    .info-card .value {
        color: var(--kick-text-primary);
        font-weight: 500;
        font-size: 0.8125rem;
        display: block;
        margin-bottom: 0.375rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .heading-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 0.625rem;
        margin-bottom: 1.25rem;
    }

    @media (max-width: 640px) {
        .heading-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .heading-count {
        background: #f9fafb;
        border: 1px solid var(--kick-border-subtle);
        border-radius: var(--kick-radius-sm);
        padding: 0.75rem;
        text-align: center;
    }

    .heading-count.warning {
        border-color: var(--kick-live-red);
    }

    .heading-count span {
        font-size: 0.625rem;
        font-weight: 700;
        color: var(--kick-text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: block;
        margin-bottom: 0.25rem;
    }

    .heading-count h3 {
        color: var(--kick-text-primary);
        font-weight: 700;
        margin: 0;
        font-size: 1.25rem;
    }

    .heading-tree {
        background: #f9fafb;
        border: 1px solid var(--kick-border-subtle);
        border-radius: var(--kick-radius-md);
        padding: 0.875rem;
        max-height: 450px;
        overflow-y: auto;
    }

    .heading-tree::-webkit-scrollbar {
        width: 4px;
    }

    .heading-tree::-webkit-scrollbar-thumb {
        background: var(--kick-border-subtle);
        border-radius: 2px;
    }

    .heading-item {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        padding: 0.375rem 0.5rem;
        border-radius: var(--kick-radius-sm);
        transition: background 0.15s;
    }

    .heading-item:hover {
        background: #f3f4f6;
    }

    .heading-badge {
        width: 32px;
        height: 22px;
        border-radius: var(--kick-radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.5625rem;
        font-weight: 700;
        color: var(--kick-text-on-accent);
        flex-shrink: 0;
        background: var(--kick-green);
    }

    .heading-badge.h2 { background: var(--kick-purple); }
    .heading-badge.h3, .heading-badge.h4, .heading-badge.h5, .heading-badge.h6 { background: #f3f4f6; color: #6b7280; }

    .heading-text {
        color: var(--kick-text-primary);
        font-weight: 500;
        font-size: 0.8125rem;
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 0.875rem;
        margin-bottom: 1.25rem;
    }

    .stat-box {
        background: #f9fafb;
        border: 1px solid var(--kick-border-subtle);
        border-radius: var(--kick-radius-sm);
        padding: 0.875rem;
        text-align: center;
    }

    .stat-box span {
        font-size: 0.625rem;
        font-weight: 700;
        color: var(--kick-text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: block;
        margin-bottom: 0.25rem;
    }

    .stat-box h3 {
        font-weight: 700;
        margin: 0;
        color: var(--kick-text-primary);
        font-size: 1.25rem;
    }

    .table-container {
        background: #f9fafb;
        border: 1px solid var(--kick-border-subtle);
        border-radius: var(--kick-radius-md);
        overflow: hidden;
        max-height: 400px;
        overflow-y: auto;
    }

    .table-container::-webkit-scrollbar {
        width: 4px;
    }

    .table-container::-webkit-scrollbar-thumb {
        background: var(--kick-border-subtle);
        border-radius: 2px;
    }

    .custom-table {
        width: 100%;
        border-collapse: collapse;
    }

    .custom-table thead {
        background: #f3f4f6;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .custom-table th {
        padding: 0.625rem 0.875rem;
        text-align: left;
        font-size: 0.625rem;
        font-weight: 700;
        color: var(--kick-text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .custom-table td {
        padding: 0.625rem 0.875rem;
        border-top: 1px solid var(--kick-border-subtle);
        color: var(--kick-text-primary);
        font-size: 0.8125rem;
    }

    .custom-table tbody tr {
        transition: background 0.15s;
    }

    .custom-table tbody tr:hover {
        background: #f3f4f6;
    }

    .image-preview {
        width: 44px;
        height: 44px;
        border-radius: var(--kick-radius-sm);
        background: #f3f4f6;
        border: 1px solid var(--kick-border-subtle);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .text-truncate-custom {
        max-width: 280px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .social-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    @media (max-width: 768px) {
        .social-grid {
            grid-template-columns: 1fr;
        }
    }

    .social-section h6 {
        color: var(--kick-text-primary);
        font-weight: 700;
        margin-bottom: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9375rem;
    }

    .social-details {
        background: #f9fafb;
        border: 1px solid var(--kick-border-subtle);
        border-radius: var(--kick-radius-sm);
        padding: 0.625rem;
        margin-bottom: 0.875rem;
    }

    .social-detail-row {
        display: flex;
        padding: 0.375rem 0;
        border-bottom: 1px solid var(--kick-border-subtle);
    }

    .social-detail-row:last-child {
        border-bottom: none;
    }

    .social-detail-label {
        width: 100px;
        color: var(--kick-text-secondary);
        font-weight: 700;
        font-size: 0.6875rem;
        flex-shrink: 0;
    }

    .social-detail-value {
        color: var(--kick-text-primary);
        font-size: 0.8125rem;
    }

    .social-preview {
        background: #ffffff;
        border: 1px solid var(--kick-border-subtle);
        border-radius: var(--kick-radius-md);
        overflow: hidden;
        max-width: 450px;
    }

    .social-preview-image {
        height: 180px;
        background: #f9fafb;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .social-preview-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .social-preview-image .placeholder {
        color: var(--kick-text-secondary);
        text-align: center;
    }

    .social-preview-image .placeholder i {
        font-size: 2.5rem;
        opacity: 0.5;
        display: block;
        margin-bottom: 0.375rem;
    }

    .social-preview-content {
        padding: 0.875rem;
        background: #f9fafb;
    }

    .social-preview-content .domain {
        color: var(--kick-text-secondary);
        font-size: 0.625rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: block;
        margin-bottom: 0.25rem;
        font-weight: 600;
    }

    .social-preview-content h6 {
        color: var(--kick-text-primary);
        font-weight: 700;
        margin-bottom: 0.25rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 0.875rem;
    }

    .social-preview-content p {
        color: var(--kick-text-secondary);
        font-size: 0.75rem;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .schema-accordion {
        display: flex;
        flex-direction: column;
        gap: 0.875rem;
    }

    .schema-item {
        background: #ffffff;
        border: 1px solid var(--kick-border-subtle);
        border-radius: var(--kick-radius-md);
        overflow: hidden;
    }

    .schema-header {
        padding: 0.875rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.625rem;
        cursor: pointer;
        transition: background 0.15s;
    }

    .schema-header:hover {
        background: #f9fafb;
    }

    .schema-header i:first-child {
        color: var(--kick-green);
        font-size: 1rem;
    }

    .schema-header span {
        color: var(--kick-text-primary);
        font-weight: 700;
        flex: 1;
        font-size: 0.875rem;
    }

    .schema-header i:last-child {
        color: var(--kick-text-secondary);
        transition: transform 0.2s;
    }

    .schema-content {
        display: none;
        padding: 1rem;
        background: #f9fafb;
        position: relative;
    }

    .schema-content.active {
        display: block;
    }

    .btn-copy {
        position: absolute;
        top: 0.875rem;
        right: 0.875rem;
        background: #f3f4f6;
        border: 1px solid var(--kick-border-subtle);
        color: var(--kick-text-secondary);
        padding: 0.25rem 0.625rem;
        border-radius: var(--kick-radius-sm);
        font-size: 0.6875rem;
        cursor: pointer;
        transition: all 0.15s;
        font-family: var(--kick-font-primary);
    }

    .btn-copy:hover {
        background: var(--kick-green);
        border-color: var(--kick-green);
        color: var(--kick-text-on-accent);
    }

    .code-block {
        background: #ffffff;
        border: 1px solid var(--kick-border-subtle);
        border-radius: var(--kick-radius-sm);
        padding: 0.875rem;
        font-family: 'Courier New', monospace;
        font-size: 0.75rem;
        color: var(--kick-text-primary);
        max-height: 350px;
        overflow: auto;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .code-block::-webkit-scrollbar {
        width: 4px;
        height: 4px;
    }

    .code-block::-webkit-scrollbar-thumb {
        background: var(--kick-border-subtle);
        border-radius: 2px;
    }

    .empty-state {
        text-align: center;
        padding: 2.5rem 1.5rem;
        background: #f9fafb;
        border: 1px solid var(--kick-border-subtle);
        border-radius: var(--kick-radius-md);
    }

    .empty-state i {
        font-size: 2.5rem;
        color: var(--kick-text-secondary);
        opacity: 0.5;
        margin-bottom: 0.875rem;
    }

    .empty-state h5 {
        color: var(--kick-text-primary);
        font-weight: 700;
        margin-bottom: 0.375rem;
        font-size: 1rem;
    }

    .empty-state p {
        color: var(--kick-text-secondary);
        max-width: 450px;
        margin: 0 auto;
        font-size: 0.8125rem;
        line-height: 1.5;
    }

    .alert-inline {
        background: rgba(239, 68, 68, 0.05);
        border: 1px solid rgba(239, 68, 68, 0.2);
        border-radius: var(--kick-radius-md);
        padding: 0.875rem;
        display: flex;
        align-items: flex-start;
        gap: 0.625rem;
        margin-bottom: 1.25rem;
    }

    .alert-inline i {
        color: var(--kick-live-red);
        font-size: 1.125rem;
        flex-shrink: 0;
    }

    .alert-inline strong {
        color: var(--kick-text-primary);
        display: block;
        margin-bottom: 0.125rem;
        font-size: 0.875rem;
        font-weight: 700;
    }

    .alert-inline p {
        color: var(--kick-text-secondary);
        margin: 0;
        font-size: 0.75rem;
        line-height: 1.5;
    }

    .alert-inline.alert-warning {
        background: rgba(239, 68, 68, 0.05);
        border-color: rgba(239, 68, 68, 0.2);
    }

    .alert-inline.alert-warning i {
        color: var(--kick-live-red);
    }

    .alert-inline.alert-success {
        background: rgba(16, 185, 129, 0.05);
        border-color: rgba(16, 185, 129, 0.2);
    }

    .alert-inline.alert-success i {
        color: var(--kick-green);
    }

    @media print {
        header, footer, nav, .sidebar-card, .search-box, .btn-analyze, .filter-group {
            display: none !important;
        }
        .layout-grid {
            grid-template-columns: 1fr !important;
        }
        .tab-pane {
            display: block !important;
        }
    }
</style>

@if($errors->any())
<div class="analyzer-wrapper">
    <div class="alert-kick alert-error">
        <i class="bi bi-exclamation-triangle-fill alert-icon"></i>
        <div class="alert-content">
            <h4>Erreur de chargement</h4>
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="filter: invert(1);"></button>
    </div>
</div>
@endif

<div class="analyzer-wrapper">
    <!-- Header Section -->
    <div class="analyzer-header">
        <span class="badge-premium">
            <i class="bi bi-lightning-charge-fill me-1"></i> Instant On-Page SEO
        </span>
        <h1>Detailed SEO Extension</h1>
        <p>Analysez instantanément n'importe quelle page web. Obtenez les métadonnées, la structure des titres, les images et les balises social en temps réel.</p>
        
        <!-- Search bar -->
        <div class="search-container">
            <form action="{{ route('seo.analyzer.analyze') }}" method="POST" id="analyzerForm" onsubmit="showLoadingState()">
                @csrf
                <div class="search-box">
                    <div class="search-icon">
                        <i class="bi bi-globe"></i>
                    </div>
                    <input type="url" name="url" value="{{ $currentProject?->url ?? '' }}" class="search-input" style="background: #f3f4f6; color: #6b7280;" readonly placeholder="{{ $currentProject ? 'Select a project in the sidebar' : 'Create a project first' }}" required>
                    <button type="submit" class="btn btn-analyze" {{ !$currentProject ? 'disabled' : '' }}>
                        <span class="btn-text">Analyser</span>
                        <div class="spinner-border spinner-border-sm ms-2 d-none" role="status" id="btnSpinner"></div>
                    </button>
                </div>
            </form>
            <div class="search-examples">
                <span>Active project:</span>
                <strong>{{ $currentProject?->name ?? 'None' }}</strong>
                <span>•</span>
                <a href="{{ route('projects.index') }}">Change project</a>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay d-none">
        <div class="loading-spinner"></div>
        <h4>Scraping et Analyse en cours...</h4>
        <p>Nous inspectons la structure HTML, les balises meta, les images, les liens et les schémas structurés...</p>
    </div>

    <!-- Results Section -->
    @if(isset($analysis))
    <div id="resultsContainer" class="results-container">
        <!-- Summary Alert Header -->
        <div class="alert-kick alert-success">
            <div class="alert-icon">
                <i class="bi bi-shield-check"></i>
            </div>
            <div class="alert-content">
                <h4>Analyse terminée</h4>
                <p>Cible : <a href="{{ $analysis['url'] }}" target="_blank">{{ $analysis['url'] }} <i class="bi bi-box-arrow-up-right small"></i></a></p>
            </div>
            <div class="alert-actions">
                <button class="btn-kick-outline" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Imprimer
                </button>
                <a href="{{ route('seo.analyzer.index') }}" class="btn-kick-outline">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Nouvelle
                </a>
            </div>
        </div>

        <!-- Main Layout -->
        <div class="layout-grid">
            <!-- Sidebar Navigation -->
            <div class="sidebar-card">
                <div class="sidebar-title">SEO Tabs</div>
                <div class="nav-tabs-kick" id="seoTabs" role="tablist">
                    <button class="nav-tab-kick active" data-bs-toggle="tab" data-bs-target="#panel-diagnostics" type="button" role="tab">
                        <i class="bi bi-shield-check"></i>
                        Audit & Diagnostics
                        @if(count($analysis['diagnostics']['errors']) > 0)
                            <span class="badge-count badge-danger">{{ count($analysis['diagnostics']['errors']) }}</span>
                        @endif
                    </button>
                    <button class="nav-tab-kick" data-bs-toggle="tab" data-bs-target="#panel-general" type="button" role="tab">
                        <i class="bi bi-info-circle"></i>
                        Général
                    </button>
                    <button class="nav-tab-kick" data-bs-toggle="tab" data-bs-target="#panel-headings" type="button" role="tab">
                        <i class="bi bi-blockquote-left"></i>
                        Titres (H1-H6)
                        <span class="badge-count">{{ count($analysis['headings']['list']) }}</span>
                    </button>
                    <button class="nav-tab-kick" data-bs-toggle="tab" data-bs-target="#panel-images" type="button" role="tab">
                        <i class="bi bi-image"></i>
                        Images
                        @if($analysis['images']['missing_alt'] > 0)
                            <span class="badge-count badge-danger">{{ $analysis['images']['missing_alt'] }}</span>
                        @else
                            <span class="badge-count">{{ $analysis['images']['total'] }}</span>
                        @endif
                    </button>
                    <button class="nav-tab-kick" data-bs-toggle="tab" data-bs-target="#panel-links" type="button" role="tab">
                        <i class="bi bi-link-45deg"></i>
                        Liens
                        <span class="badge-count">{{ $analysis['links']['total'] }}</span>
                    </button>
                    <button class="nav-tab-kick" data-bs-toggle="tab" data-bs-target="#panel-social" type="button" role="tab">
                        <i class="bi bi-share"></i>
                        Social (OG & Twitter)
                    </button>
                    <button class="nav-tab-kick" data-bs-toggle="tab" data-bs-target="#panel-schema" type="button" role="tab">
                        <i class="bi bi-code-slash"></i>
                        Schémas (LD-JSON)
                        <span class="badge-count">{{ count($analysis['schemas']) }}</span>
                    </button>
                    @if(isset($analysis['pagespeed']) && $analysis['pagespeed']['available'])
                    <button class="nav-tab-kick" data-bs-toggle="tab" data-bs-target="#panel-pagespeed" type="button" role="tab">
                        <i class="bi bi-speedometer2"></i>
                        PageSpeed
                    </button>
                    @endif
                </div>
            </div>

            <!-- Content Area -->
            <div class="content-area">
                <div class="tab-content" id="seoTabsContent">
                    
                    <!-- DIAGNOSTICS TAB -->
                    <div class="tab-pane fade show active" id="panel-diagnostics" role="tabpanel">
                        <!-- Score Section -->
                        <div class="content-card">
                            <div class="score-section">
                                <div class="score-ring">
                                    <svg width="120" height="120">
                                        <circle class="bg-ring" cx="60" cy="60" r="50" />
                                        <circle class="progress-ring" cx="60" cy="60" r="50" stroke="{{ $analysis['diagnostics']['score'] >= 80 ? '#10b981' : ($analysis['diagnostics']['score'] >= 50 ? '#ef4444' : '#ef4444') }}" stroke-dashoffset="{{ 314 - (314 * $analysis['diagnostics']['score']) / 100 }}" />
                                    </svg>
                                    <div class="score-value">
                                        <h2 style="color: {{ $analysis['diagnostics']['score'] >= 80 ? '#10b981' : ($analysis['diagnostics']['score'] >= 50 ? '#ef4444' : '#ef4444') }}">{{ $analysis['diagnostics']['score'] }}</h2>
                                        <small>Score SEO</small>
                                    </div>
                                </div>
                                <div class="score-info">
                                    <span class="badge-premium">
                                        <i class="bi bi-shield-check me-1"></i> Audit On-Page
                                    </span>
                                    <h3>Rapport de Diagnostic</h3>
                                    <p>
                                        @if($analysis['diagnostics']['score'] >= 80)
                                            Excellent travail ! Cette page présente de très bonnes bases pour le référencement naturel.
                                        @elseif($analysis['diagnostics']['score'] >= 50)
                                            Des optimisations importantes sont nécessaires. Corrigez les erreurs critiques.
                                        @else
                                            Attention ! Cette page possède de graves lacunes SEO. Suivez nos conseils ci-dessous.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Severity Cards -->
                        <div class="severity-grid">
                            <div class="severity-card errors">
                                <div class="severity-header">
                                    <h6>Erreurs</h6>
                                    <div class="severity-icon"><i class="bi bi-x-lg"></i></div>
                                </div>
                                <div class="severity-count">{{ count($analysis['diagnostics']['errors']) }}</div>
                                <div class="severity-desc">Problèmes majeurs bloquant le référencement.</div>
                            </div>
                            <div class="severity-card warnings">
                                <div class="severity-header">
                                    <h6>Avertissements</h6>
                                    <div class="severity-icon"><i class="bi bi-exclamation-triangle"></i></div>
                                </div>
                                <div class="severity-count">{{ count($analysis['diagnostics']['warnings']) }}</div>
                                <div class="severity-desc">Améliorations recommandées.</div>
                            </div>
                            <div class="severity-card passed">
                                <div class="severity-header">
                                    <h6>Réussis</h6>
                                    <div class="severity-icon"><i class="bi bi-check-circle"></i></div>
                                </div>
                                <div class="severity-count">{{ count($analysis['diagnostics']['successes']) }}</div>
                                <div class="severity-desc">Éléments optimisés.</div>
                            </div>
                        </div>

                        <!-- Diagnostics List -->
                        <div class="content-card">
                            <div class="filter-bar">
                                <h5>Recommandations SEO</h5>
                                <div class="filter-group">
                                    <button class="btn-filter active" onclick="filterDiagnostics('all', this)">Tout</button>
                                    <button class="btn-filter" onclick="filterDiagnostics('errors', this)">Erreurs ({{ count($analysis['diagnostics']['errors']) }})</button>
                                    <button class="btn-filter" onclick="filterDiagnostics('warnings', this)">Avertissements ({{ count($analysis['diagnostics']['warnings']) }})</button>
                                    <button class="btn-filter" onclick="filterDiagnostics('successes', this)">Réussis ({{ count($analysis['diagnostics']['successes']) }})</button>
                                </div>
                            </div>

                            <div class="diagnostics-list">
                                @foreach($analysis['diagnostics']['errors'] as $error)
                                    <div class="diagnostic-item error">
                                        <div class="diagnostic-header">
                                            <span class="badge-severity error"><i class="bi bi-x-circle me-1"></i> Erreur</span>
                                            <span class="badge-category">{{ $error['category'] }}</span>
                                        </div>
                                        <h5>{{ $error['title'] }}</h5>
                                        <p>{{ $error['description'] }}</p>
                                        <div class="recommendation-box">
                                            <i class="bi bi-lightbulb-fill icon"></i>
                                            <strong>Comment corriger :</strong>
                                            <span>{!! $error['recommendation'] !!}</span>
                                        </div>
                                    </div>
                                @endforeach

                                @foreach($analysis['diagnostics']['warnings'] as $warning)
                                    <div class="diagnostic-item warning">
                                        <div class="diagnostic-header">
                                            <span class="badge-severity warning"><i class="bi bi-exclamation-triangle me-1"></i> Avertissement</span>
                                            <span class="badge-category">{{ $warning['category'] }}</span>
                                        </div>
                                        <h5>{{ $warning['title'] }}</h5>
                                        <p>{{ $warning['description'] }}</p>
                                        <div class="recommendation-box">
                                            <i class="bi bi-lightbulb-fill icon"></i>
                                            <strong>Comment corriger :</strong>
                                            <span>{!! $warning['recommendation'] !!}</span>
                                        </div>
                                    </div>
                                @endforeach

                                @foreach($analysis['diagnostics']['successes'] as $success)
                                    <div class="diagnostic-item success">
                                        <div class="diagnostic-header">
                                            <span class="badge-severity success"><i class="bi bi-check-circle me-1"></i> Réussi</span>
                                            <span class="badge-category">{{ $success['category'] }}</span>
                                        </div>
                                        <h5>{{ $success['title'] }}</h5>
                                        <p>{{ $success['description'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- GENERAL TAB -->
                    <div class="tab-pane fade" id="panel-general" role="tabpanel">
                        <div class="content-card">
                            <h4>Informations Générales</h4>
                            
                            <!-- Title Tag -->
                            <div class="metric-row">
                                <div class="metric-header">
                                    <label>Balise Title</label>
                                    @if($analysis['title']['status'] === 'optimal')
                                        <span class="metric-value optimal">Optimisé ({{ $analysis['title']['length'] }} car.)</span>
                                    @elseif($analysis['title']['status'] === 'short')
                                        <span class="metric-value short">Court ({{ $analysis['title']['length'] }} car.)</span>
                                    @elseif($analysis['title']['status'] === 'long')
                                        <span class="metric-value long">Long ({{ $analysis['title']['length'] }} car.)</span>
                                    @else
                                        <span class="metric-value missing">Manquant</span>
                                    @endif
                                </div>
                                <div class="metric-display">
                                    <span>{{ $analysis['title']['text'] ?: 'Aucune balise de titre trouvée !' }}</span>
                                </div>
                                <div class="metric-footer">
                                    <small>Recommandation: 30 à 60 caractères.</small>
                                    <small class="progress-value">{{ $analysis['title']['length'] }} / 60</small>
                                </div>
                                <div class="progress-bar-kick">
                                    <div class="progress-bar-fill" style="width: {{ min(100, ($analysis['title']['length'] / 60) * 100) }}%; background-color: {{ $analysis['title']['status'] === 'optimal' ? '#10b981' : ($analysis['title']['status'] === 'long' ? '#ef4444' : '#9ca3af') }};"></div>
                                </div>
                            </div>

                            <!-- Description Tag -->
                            <div class="metric-row">
                                <div class="metric-header">
                                    <label>Méta Description</label>
                                    @if($analysis['description']['status'] === 'optimal')
                                        <span class="metric-value optimal">Optimisé ({{ $analysis['description']['length'] }} car.)</span>
                                    @elseif($analysis['description']['status'] === 'short')
                                        <span class="metric-value short">Court ({{ $analysis['description']['length'] }} car.)</span>
                                    @elseif($analysis['description']['status'] === 'long')
                                        <span class="metric-value long">Long ({{ $analysis['description']['length'] }} car.)</span>
                                    @else
                                        <span class="metric-value missing">Manquant</span>
                                    @endif
                                </div>
                                <div class="metric-display">
                                    <span>{{ $analysis['description']['text'] ?: 'Aucune description meta trouvée !' }}</span>
                                </div>
                                <div class="metric-footer">
                                    <small>Recommandation: 110 à 160 caractères.</small>
                                    <small class="progress-value">{{ $analysis['description']['length'] }} / 160</small>
                                </div>
                                <div class="progress-bar-kick">
                                    <div class="progress-bar-fill" style="width: {{ min(100, ($analysis['description']['length'] / 160) * 100) }}%; background-color: {{ $analysis['description']['status'] === 'optimal' ? '#10b981' : ($analysis['description']['status'] === 'long' ? '#ef4444' : '#9ca3af') }};"></div>
                                </div>
                            </div>

                            <!-- Canonical & Robots -->
                            <div class="info-grid">
                                <div class="info-card">
                                    <label>URL Canonique</label>
                                    <span class="value" title="{{ $analysis['canonical'] }}">{{ $analysis['canonical'] ?: 'Non définie' }}</span>
                                    @if(!empty($analysis['canonical']))
                                        @if($analysis['canonical_matches'])
                                            <small style="color: #10b981;"><i class="bi bi-check-circle-fill"></i> Correspond</small>
                                        @else
                                            <small style="color: #ef4444;"><i class="bi bi-exclamation-triangle-fill"></i> Diffère</small>
                                        @endif
                                    @else
                                        <small style="color: #ef4444;"><i class="bi bi-x-circle-fill"></i> Manquant</small>
                                    @endif
                                </div>
                                <div class="info-card">
                                    <label>Meta Robots</label>
                                    <span class="value">{{ $analysis['robots'] ?: 'Index, Follow' }}</span>
                                    @if(strpos(strtolower($analysis['robots']), 'noindex') !== false)
                                        <span class="metric-value missing" style="display: inline-block;">NoIndex</span>
                                    @else
                                        <span class="metric-value optimal" style="display: inline-block;">Indexable</span>
                                    @endif
                                </div>
                                <div class="info-card">
                                    <label>X-Robots-Tag</label>
                                    <span class="value">{{ $analysis['x_robots'] ?: 'Non envoyé' }}</span>
                                </div>
                                <div class="info-card">
                                    <label>Auteur & Technologies</label>
                                    <span class="value"><i class="bi bi-person me-1"></i> {{ $analysis['author'] ?: 'Non spécifié' }}</span>
                                    <span class="value" style="margin-top: 0.25rem;"><i class="bi bi-cpu me-1"></i> {{ $analysis['generator'] ?: 'Inconnu' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- HEADINGS TAB -->
                    <div class="tab-pane fade" id="panel-headings" role="tabpanel">
                        <div class="content-card">
                            <h4>Structure des Titres (H1-H6)</h4>
                            
                            <div class="heading-grid">
                                @foreach(range(1, 6) as $i)
                                    @php $tag = 'h'.$i; $count = $analysis['headings']['counts'][$tag]; @endphp
                                    <div class="heading-count {{ $tag === 'h1' && $count !== 1 ? 'warning' : '' }}">
                                        <span>H{{ $i }}</span>
                                        <h3>{{ $count }}</h3>
                                    </div>
                                @endforeach
                            </div>

                            @if($analysis['headings']['counts']['h1'] === 0)
                                <div class="alert-inline">
                                    <i class="bi bi-x-octagon-fill"></i>
                                    <div>
                                        <strong>Balise H1 manquante !</strong>
                                        <p>Chaque page doit avoir exactement une balise H1.</p>
                                    </div>
                                </div>
                            @elseif($analysis['headings']['counts']['h1'] > 1)
                                <div class="alert-inline alert-warning">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <div>
                                        <strong>Plusieurs H1 détectées ({{ $analysis['headings']['counts']['h1'] }}) !</strong>
                                        <p>Une seule balise H1 par page est recommandée.</p>
                                    </div>
                                </div>
                            @endif

                            <h6 style="color: var(--kick-text-secondary); font-weight: 700; margin-bottom: 0.875rem; font-size: 0.8125rem; text-transform: uppercase;">Hiérarchie du document</h6>
                            @if(empty($analysis['headings']['list']))
                                <div class="empty-state">
                                    <i class="bi bi-blockquote-left"></i>
                                    <h5>Aucun titre trouvé</h5>
                                    <p>Aucun titre (H1-H6) n'a été trouvé.</p>
                                </div>
                            @else
                                <div class="heading-tree">
                                    @foreach($analysis['headings']['list'] as $heading)
                                        @php
                                            $level = intval(substr($heading['tag'], 1));
                                            $padding = ($level - 1) * 16;
                                        @endphp
                                        <div class="heading-item" style="padding-left: {{ $padding + 8 }}px;">
                                            <span class="heading-badge {{ $heading['tag'] }}">{{ $heading['tag'] }}</span>
                                            <span class="heading-text">{{ $heading['text'] ?: '[Titre vide]' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- IMAGES TAB -->
                    <div class="tab-pane fade" id="panel-images" role="tabpanel">
                        <div class="content-card">
                            <h4>Analyse des Images</h4>

                            <div class="stats-row">
                                <div class="stat-box">
                                    <span>Total</span>
                                    <h3>{{ $analysis['images']['total'] }}</h3>
                                </div>
                                <div class="stat-box">
                                    <span>Alt OK</span>
                                    <h3 style="color: #10b981;">{{ $analysis['images']['with_alt'] }}</h3>
                                </div>
                                <div class="stat-box">
                                    <span>Alt Manquant</span>
                                    <h3 style="color: #ef4444;">{{ $analysis['images']['missing_alt'] }}</h3>
                                </div>
                            </div>

                            @if($analysis['images']['missing_alt'] > 0)
                                <div class="alert-inline alert-warning">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <div>
                                        <strong>{{ $analysis['images']['missing_alt'] }} images sans alt</strong>
                                        <p>L'attribut alt est essentiel pour l'accessibilité et le SEO.</p>
                                    </div>
                                </div>
                            @endif

                            <div class="filter-bar">
                                <h6 style="color: var(--kick-text-primary); font-weight: 700; margin: 0; font-size: 0.8125rem;">Liste des images</h6>
                                <div class="filter-group">
                                    <button class="btn-filter active" onclick="filterImages('all', this)">Toutes</button>
                                    <button class="btn-filter" onclick="filterImages('missing', this)">Alt manquant</button>
                                </div>
                            </div>

                            @if(empty($analysis['images']['list']))
                                <div class="empty-state">
                                    <i class="bi bi-images"></i>
                                    <h5>Aucune image</h5>
                                    <p>Aucune image détectée.</p>
                                </div>
                            @else
                                <div class="table-container">
                                    <table class="custom-table" id="imagesTable">
                                        <thead>
                                            <tr>
                                                <th style="width: 60px;">Visuel</th>
                                                <th>URL Source</th>
                                                <th>Alt Text</th>
                                                <th style="text-align: right;">Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($analysis['images']['list'] as $img)
                                                <tr class="{{ $img['has_alt'] ? 'img-ok' : 'img-missing-alt' }}">
                                                    <td>
                                                        <div class="image-preview">
                                                            <img src="{{ $img['src'] }}" alt="preview" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'44\' height=\'44\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%239DA3AF\' stroke-width=\'2\'><rect x=\'3\' y=\'3\' width=\'18\' height=\'18\' rx=\'2\'/><circle cx=\'8.5\' cy=\'8.5\' r=\'1.5\'/><path d=\'M21 15l-5-5L5 21\'/></svg>'">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate-custom" title="{{ $img['src'] }}">
                                                            <a href="{{ $img['src'] }}" target="_blank" style="color: var(--kick-green); text-decoration: none;">{{ $img['src'] }}</a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($img['has_alt'])
                                                            <span style="color: var(--kick-text-primary); font-weight: 500;">{{ $img['alt'] }}</span>
                                                        @else
                                                            <span style="color: var(--kick-live-red); font-style: italic;">Manquant</span>
                                                        @endif
                                                    </td>
                                                    <td style="text-align: right;">
                                                        @if($img['has_alt'])
                                                            <span class="metric-value optimal">OK</span>
                                                        @else
                                                            <span class="metric-value missing">Manquant</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- LINKS TAB -->
                    <div class="tab-pane fade" id="panel-links" role="tabpanel">
                        <div class="content-card">
                            <h4>Analyse des Liens</h4>

                            <div class="stats-row">
                                <div class="stat-box">
                                    <span>Total</span>
                                    <h3>{{ $analysis['links']['total'] }}</h3>
                                </div>
                                <div class="stat-box">
                                    <span>Internes</span>
                                    <h3 style="color: #10b981;">{{ $analysis['links']['internal'] }}</h3>
                                </div>
                                <div class="stat-box">
                                    <span>Externes</span>
                                    <h3 style="color: #ef4444;">{{ $analysis['links']['external'] }}</h3>
                                </div>
                                <div class="stat-box">
                                    <span>NoFollow</span>
                                    <h3 style="color: #6b7280;">{{ $analysis['links']['nofollow'] }}</h3>
                                </div>
                            </div>

                            <div class="filter-bar">
                                <h6 style="color: var(--kick-text-primary); font-weight: 700; margin: 0; font-size: 0.8125rem;">Liens</h6>
                                <div class="filter-group">
                                    <button class="btn-filter active" onclick="filterLinks('all', this)">Tous</button>
                                    <button class="btn-filter" onclick="filterLinks('internal', this)">Internes</button>
                                    <button class="btn-filter" onclick="filterLinks('external', this)">Externes</button>
                                    <button class="btn-filter" onclick="filterLinks('nofollow', this)">NoFollow</button>
                                </div>
                            </div>

                            @if(empty($analysis['links']['list']))
                                <div class="empty-state">
                                    <i class="bi bi-link-45deg"></i>
                                    <h5>Aucun lien</h5>
                                    <p>Aucun lien détecté.</p>
                                </div>
                            @else
                                <div class="table-container">
                                    <table class="custom-table" id="linksTable">
                                        <thead>
                                            <tr>
                                                <th>Ancre</th>
                                                <th>URL</th>
                                                <th>Type</th>
                                                <th style="text-align: right;">Attribut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($analysis['links']['list'] as $link)
                                                <tr class="{{ $link['is_internal'] ? 'link-internal' : 'link-external' }} {{ $link['is_nofollow'] ? 'link-nofollow' : 'link-dofollow' }}">
                                                    <td>
                                                        <span style="color: var(--kick-text-primary); font-weight: 600;">{{ $link['text'] }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate-custom" title="{{ $link['href'] }}">
                                                            <a href="{{ $link['href'] }}" target="_blank" style="color: var(--kick-green); text-decoration: none;">{{ $link['href'] }}</a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($link['is_internal'])
                                                            <span class="metric-value" style="background: var(--kick-green); color: var(--kick-text-on-accent);">Interne</span>
                                                        @else
                                                            <span class="metric-value short">Externe</span>
                                                        @endif
                                                    </td>
                                                    <td style="text-align: right;">
                                                        @if($link['is_nofollow'])
                                                            <span class="metric-value" style="background: #f3f4f6; color: #6b7280;">nofollow</span>
                                                        @else
                                                            <span class="metric-value optimal">dofollow</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- SOCIAL TAB -->
                    <div class="tab-pane fade" id="panel-social" role="tabpanel">
                        <div class="content-card">
                            <h4>Balises Réseaux Sociaux</h4>
                            
                            <!-- Open Graph -->
                            <div class="social-section">
                                <h6><i class="bi bi-facebook" style="color: var(--kick-purple);"></i> Open Graph / Facebook</h6>
                                <div class="social-grid">
                                    <div>
                                        <div class="social-details">
                                            <div class="social-detail-row">
                                                <span class="social-detail-label">og:title</span>
                                                <span class="social-detail-value">{{ $analysis['social']['og']['title'] ?: 'Non configuré' }}</span>
                                            </div>
                                            <div class="social-detail-row">
                                                <span class="social-detail-label">og:description</span>
                                                <span class="social-detail-value">{{ Str::limit($analysis['social']['og']['description'], 80) ?: 'Non configuré' }}</span>
                                            </div>
                                            <div class="social-detail-row">
                                                <span class="social-detail-label">og:type</span>
                                                <span class="social-detail-value">{{ $analysis['social']['og']['type'] ?: 'website' }}</span>
                                            </div>
                                            <div class="social-detail-row">
                                                <span class="social-detail-label">og:site_name</span>
                                                <span class="social-detail-value">{{ $analysis['social']['og']['site_name'] ?: 'Non configuré' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="social-preview">
                                            @if(!empty($analysis['social']['og']['image']))
                                                <div class="social-preview-image">
                                                    <img src="{{ $analysis['social']['og']['image'] }}" alt="og:image">
                                                </div>
                                            @else
                                                <div class="social-preview-image">
                                                    <div class="placeholder">
                                                        <i class="bi bi-image"></i>
                                                        <span>Aucune image</span>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="social-preview-content">
                                                <span class="domain">{{ parse_url($analysis['social']['og']['url'] ?: $analysis['url'], PHP_URL_HOST) }}</span>
                                                <h6>{{ $analysis['social']['og']['title'] ?: $analysis['title']['text'] }}</h6>
                                                <p>{{ $analysis['social']['og']['description'] ?: $analysis['description']['text'] ?: 'Aucune description.' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Twitter Card -->
                            <div class="social-section">
                                <h6><i class="bi bi-twitter-x"></i> Twitter / X Cards</h6>
                                <div class="social-grid">
                                    <div>
                                        <div class="social-details">
                                            <div class="social-detail-row">
                                                <span class="social-detail-label">twitter:card</span>
                                                <span class="social-detail-value">{{ $analysis['social']['twitter']['card'] ?: 'summary' }}</span>
                                            </div>
                                            <div class="social-detail-row">
                                                <span class="social-detail-label">twitter:title</span>
                                                <span class="social-detail-value">{{ $analysis['social']['twitter']['title'] ?: 'Non configuré' }}</span>
                                            </div>
                                            <div class="social-detail-row">
                                                <span class="social-detail-label">twitter:description</span>
                                                <span class="social-detail-value">{{ Str::limit($analysis['social']['twitter']['description'], 80) ?: 'Non configuré' }}</span>
                                            </div>
                                            <div class="social-detail-row">
                                                <span class="social-detail-label">twitter:site</span>
                                                <span class="social-detail-value">{{ $analysis['social']['twitter']['site'] ?: 'Non configuré' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="social-preview">
                                            @if(!empty($analysis['social']['twitter']['image']))
                                                <div class="social-preview-image">
                                                    <img src="{{ $analysis['social']['twitter']['image'] }}" alt="twitter:image">
                                                </div>
                                            @else
                                                <div class="social-preview-image">
                                                    <div class="placeholder">
                                                        <i class="bi bi-image"></i>
                                                        <span>Aucune image</span>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="social-preview-content">
                                                <span class="domain">{{ parse_url($analysis['url'], PHP_URL_HOST) }}</span>
                                                <h6>{{ $analysis['social']['twitter']['title'] ?: $analysis['title']['text'] }}</h6>
                                                <p>{{ $analysis['social']['twitter']['description'] ?: $analysis['description']['text'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SCHEMA TAB -->
                    <div class="tab-pane fade" id="panel-schema" role="tabpanel">
                        <div class="content-card">
                            <h4>Données Structurées (JSON-LD)</h4>

                            @if(empty($analysis['schemas']))
                                <div class="empty-state">
                                    <i class="bi bi-code-slash"></i>
                                    <h5>Aucun schéma JSON-LD</h5>
                                    <p>Les données structurées aident Google à mieux comprendre votre contenu.</p>
                                </div>
                            @else
                                <div class="alert-inline alert-success">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <div>
                                        <strong>{{ count($analysis['schemas']) }} blocs trouvés</strong>
                                        <p>Améliore la visibilité dans les résultats de recherche.</p>
                                    </div>
                                </div>

                                <div class="schema-accordion">
                                    @foreach($analysis['schemas'] as $index => $schema)
                                        @php
                                            $schemaType = $schema['@type'] ?? ($schema['@graph'][0]['@type'] ?? 'Structured Data');
                                            if (is_array($schemaType)) {
                                                $schemaType = implode(', ', $schemaType);
                                            }
                                        @endphp
                                        <div class="schema-item">
                                            <div class="schema-header" onclick="toggleSchema({{ $index }})">
                                                <i class="bi bi-braces"></i>
                                                <span>{{ $schemaType }}</span>
                                                <i class="bi bi-chevron-down" id="schema-icon-{{ $index }}"></i>
                                            </div>
                                            <div class="schema-content" id="schema-content-{{ $index }}">
                                                <button class="btn-copy" onclick="copyToClipboard('schema-code-{{ $index }}')">
                                                    <i class="bi bi-copy"></i> Copier
                                                </button>
                                                <div class="code-block" id="schema-code-{{ $index }}">{{ json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- PAGESPEED TAB -->
                    @if(isset($analysis['pagespeed']) && $analysis['pagespeed']['available'])
                    <div class="tab-pane fade" id="panel-pagespeed" role="tabpanel">
                        <div class="content-card">
                            <h4>PageSpeed Insights (Google Lighthouse)</h4>

                            <!-- Score Cards -->
                            <div class="stats-row">
                                <div class="stat-box">
                                    <span>Performance</span>
                                    <h3 style="color: {{ ($analysis['pagespeed']['scores']['performance'] ?? 0) >= 90 ? 'var(--kick-green)' : (($analysis['pagespeed']['scores']['performance'] ?? 0) >= 50 ? 'var(--kick-text-secondary)' : 'var(--kick-live-red)') }};">
                                        {{ $analysis['pagespeed']['scores']['performance'] ?? 'N/A' }}
                                    </h3>
                                </div>
                                <div class="stat-box">
                                    <span>SEO</span>
                                    <h3 style="color: {{ ($analysis['pagespeed']['scores']['seo'] ?? 0) >= 90 ? 'var(--kick-green)' : 'var(--kick-text-secondary)' }};">
                                        {{ $analysis['pagespeed']['scores']['seo'] ?? 'N/A' }}
                                    </h3>
                                </div>
                                <div class="stat-box">
                                    <span>Accessibility</span>
                                    <h3 style="color: {{ ($analysis['pagespeed']['scores']['accessibility'] ?? 0) >= 90 ? 'var(--kick-green)' : 'var(--kick-text-secondary)' }};">
                                        {{ $analysis['pagespeed']['scores']['accessibility'] ?? 'N/A' }}
                                    </h3>
                                </div>
                                <div class="stat-box">
                                    <span>Best Practices</span>
                                    <h3 style="color: {{ ($analysis['pagespeed']['scores']['best_practices'] ?? 0) >= 90 ? 'var(--kick-green)' : 'var(--kick-text-secondary)' }};">
                                        {{ $analysis['pagespeed']['scores']['best_practices'] ?? 'N/A' }}
                                    </h3>
                                </div>
                            </div>

                            <!-- Core Web Vitals -->
                            @if(isset($analysis['pagespeed']['core_vitals']))
                            <h6 style="color: var(--kick-text-secondary); font-weight: 700; margin: 1.25rem 0 0.875rem; font-size: 0.8125rem; text-transform: uppercase;">Core Web Vitals</h6>
                            <div class="stats-row">
                                @if(isset($analysis['pagespeed']['core_vitals']['lcp']))
                                <div class="stat-box">
                                    <span>LCP</span>
                                    <h3 style="font-size: 1rem;">{{ round($analysis['pagespeed']['core_vitals']['lcp'] / 1000, 1) }}s</h3>
                                </div>
                                @endif
                                @if(isset($analysis['pagespeed']['core_vitals']['fcp']))
                                <div class="stat-box">
                                    <span>FCP</span>
                                    <h3 style="font-size: 1rem;">{{ round($analysis['pagespeed']['core_vitals']['fcp'] / 1000, 1) }}s</h3>
                                </div>
                                @endif
                                @if(isset($analysis['pagespeed']['core_vitals']['cls']))
                                <div class="stat-box">
                                    <span>CLS</span>
                                    <h3 style="font-size: 1rem;">{{ number_format($analysis['pagespeed']['core_vitals']['cls'], 3) }}</h3>
                                </div>
                                @endif
                                @if(isset($analysis['pagespeed']['core_vitals']['ttfb']))
                                <div class="stat-box">
                                    <span>TTFB</span>
                                    <h3 style="font-size: 1rem;">{{ round($analysis['pagespeed']['core_vitals']['ttfb'] / 1000, 2) }}s</h3>
                                </div>
                                @endif
                                @if(isset($analysis['pagespeed']['core_vitals']['inp']))
                                <div class="stat-box">
                                    <span>INP</span>
                                    <h3 style="font-size: 1rem;">{{ $analysis['pagespeed']['core_vitals']['inp'] }}ms</h3>
                                </div>
                                @endif
                            </div>
                            @endif

                            <!-- Opportunities -->
                            @if(!empty($analysis['pagespeed']['opportunities']))
                            <h6 style="color: var(--kick-text-secondary); font-weight: 700; margin: 1.25rem 0 0.875rem; font-size: 0.8125rem; text-transform: uppercase;">Opportunities</h6>
                            @foreach($analysis['pagespeed']['opportunities'] as $opp)
                            <div class="d-flex align-items-center justify-content-between p-3 mb-2 rounded" style="background: var(--kick-surface-2);">
                                <div>
                                    <div style="font-size: 0.8125rem; font-weight: 600; color: var(--kick-text-primary);">{{ $opp['title'] ?? $opp['key'] }}</div>
                                </div>
                                @if(isset($opp['savings_ms']) && $opp['savings_ms'] > 0)
                                <span class="badge-severity" style="background: #f3f4f6; color: #6b7280;">-{{ $opp['savings_ms'] }}ms</span>
                                @endif
                            </div>
                            @endforeach
                            @endif
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
    function fillExample(url) {
        document.querySelector('input[name="url"]').value = url;
        return false;
    }

    function showLoadingState() {
        const btn = document.querySelector('.btn-analyze');
        const spinner = document.getElementById('btnSpinner');
        const btnText = btn.querySelector('.btn-text');
        
        btn.disabled = true;
        spinner.classList.remove('d-none');
        btnText.innerText = "Analyse...";
        
        document.getElementById('loadingOverlay').classList.remove('d-none');
        const results = document.getElementById('resultsContainer');
        if (results) {
            results.style.opacity = '0.5';
        }
    }

    function filterImages(type, btn) {
        const buttons = btn.parentNode.querySelectorAll('.btn-filter');
        buttons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const rows = document.querySelectorAll('#imagesTable tbody tr');
        rows.forEach(row => {
            row.style.display = (type === 'all' || (type === 'missing' && row.classList.contains('img-missing-alt'))) ? '' : 'none';
        });
    }

    function filterLinks(type, btn) {
        const buttons = btn.parentNode.querySelectorAll('.btn-filter');
        buttons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const rows = document.querySelectorAll('#linksTable tbody tr');
        rows.forEach(row => {
            let show = type === 'all';
            if (type === 'internal') show = row.classList.contains('link-internal');
            if (type === 'external') show = row.classList.contains('link-external');
            if (type === 'nofollow') show = row.classList.contains('link-nofollow');
            row.style.display = show ? '' : 'none';
        });
    }

    function filterDiagnostics(type, btn) {
        const buttons = btn.parentNode.querySelectorAll('.btn-filter');
        buttons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const items = document.querySelectorAll('.diagnostics-list .diagnostic-item');
        items.forEach(item => {
            let show = type === 'all';
            if (type === 'errors') show = item.classList.contains('error');
            if (type === 'warnings') show = item.classList.contains('warning');
            if (type === 'successes') show = item.classList.contains('success');
            item.style.display = show ? '' : 'none';
        });
    }

    function copyToClipboard(elementId) {
        const pre = document.getElementById(elementId);
        navigator.clipboard.writeText(pre.textContent).then(() => {
            const btn = pre.parentNode.querySelector('.btn-copy');
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Copié !';
            btn.style.background = 'var(--kick-green)';
            btn.style.color = 'var(--kick-text-on-accent)';
            setTimeout(() => {
                btn.innerHTML = '<i class="bi bi-copy"></i> Copier';
                btn.style.background = '';
                btn.style.color = '';
            }, 2000);
        });
    }

    function toggleSchema(index) {
        const content = document.getElementById('schema-content-' + index);
        const icon = document.getElementById('schema-icon-' + index);
        content.classList.toggle('active');
        icon.style.transform = content.classList.contains('active') ? 'rotate(180deg)' : '';
    }

    document.querySelectorAll('.nav-tab-kick').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.nav-tab-kick').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });
</script>
@endsection
