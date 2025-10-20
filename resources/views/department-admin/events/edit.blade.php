@php
use Illuminate\Support\Facades\Storage;
@endphp

@extends('layouts.app')

@section('title', 'Edit Event - Department Admin')

@push('styles')
<style>
    :root {
        --primary-color: #10b981;
        --secondary-color: #1f2937;
        --accent-color: #3b82f6;
        --success-color: #059669;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
        --text-primary: #111827;
        --text-secondary: #6b7280;
        --bg-primary: #ffffff;
        --bg-secondary: #f9fafb;
        --border-color: #e5e7eb;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        --radius-sm: 0.375rem;
        --radius-md: 0.5rem;
        --radius-lg: 0.75rem;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background: white;
        min-height: 100vh;
        margin: 0;
        color: var(--text-primary);
    }

    .dashboard {
        display: flex;
        min-height: 100vh;
    }

    .sidebar {
        width: 320px;
        background: linear-gradient(135deg, #000000 0%, #1a1a1a 50%, #2d2d2d 100%);
        color: white;
        position: fixed;
        height: 100vh;
        left: 0;
        top: 0;
        overflow-y: auto;
        overflow-x: hidden;
        z-index: 1000;
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.3), 0 0 40px rgba(0, 0, 0, 0.1);
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        backdrop-filter: blur(20px);
        border-right: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.02) 50%, transparent 70%);
        animation: shimmer 3s ease-in-out infinite;
        pointer-events: none;
    }

    @keyframes shimmer {
        0%, 100% { transform: translateX(-100%); opacity: 0; }
        50% { transform: translateX(100%); opacity: 1; }
    }

    .sidebar-header {
        padding: 2rem 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
        color: white;
        position: relative;
        overflow: hidden;
        text-align: center;
    }

    .sidebar-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, #ffffff, #e5e7eb, #ffffff);
        animation: headerShimmer 2s ease-in-out infinite;
    }

    @keyframes headerShimmer {
        0%, 100% { opacity: 0.3; }
        50% { opacity: 1; }
    }

    .sidebar-header h3 {
        font-size: 1.5rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        background: linear-gradient(135deg, #ffffff 0%, #e5e7eb 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        position: relative;
        margin: 0;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        letter-spacing: 0.5px;
        line-height: 1.2;
    }

    .sidebar-header h3 i {
        font-size: 1.5rem;
        color: #ffffff;
        background: linear-gradient(135deg, #ffffff 0%, #d1d5db 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
    }

    .sidebar-header .dept-info {
        font-size: 0.85rem;
        margin-top: 0.5rem;
        opacity: 0.85;
        color: #d1d5db;
        font-weight: 500;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        background: linear-gradient(135deg, #d1d5db 0%, #9ca3af 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
        line-height: 1.3;
    }

    .sidebar-menu {
        list-style: none;
        padding: 1.5rem 0;
        margin: 0;
    }

    .sidebar-menu li {
        margin: 0.25rem 0;
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        padding: 1rem 2rem;
        color: #d1d5db;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.95rem;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        gap: 1rem;
        position: relative;
        border-radius: 0 25px 25px 0;
        margin: 0.25rem 0;
        overflow: hidden;
        letter-spacing: 0.3px;
    }

    .sidebar-menu a::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(135deg, #ffffff, #e5e7eb);
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }

    .sidebar-menu a:hover,
    .sidebar-menu a.active {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0.05) 100%);
        color: white;
        transform: translateX(8px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        backdrop-filter: blur(10px);
    }

    .sidebar-menu a:hover::before,
    .sidebar-menu a.active::before {
        transform: scaleY(1);
    }

    .sidebar-menu a i {
        width: 18px;
        height: 18px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
        flex-shrink: 0;
    }

    .sidebar-menu a:hover i,
    .sidebar-menu a.active i {
        transform: scale(1.2) rotate(5deg);
        color: #ffffff;
    }

    .sidebar-menu a span {
        transition: all 0.3s ease;
        flex: 1;
        text-align: left;
        line-height: 1.4;
    }

    .sidebar-menu a:hover span,
    .sidebar-menu a.active span {
        font-weight: 600;
        letter-spacing: 0.5px;
        color: #ffffff;
    }


    .logout-btn {
        color: #fca5a5 !important;
    }

    .logout-btn:hover {
        background: rgba(239, 68, 68, 0.1) !important;
        color: #ef4444 !important;
        border-left-color: #ef4444 !important;
    }

    .main-content {
        flex: 1;
        margin-left: 320px;
        padding: 2rem;
        background: white;
    }

    .mobile-menu-btn {
        display: none;
    }

    .header {
        background: white;
        border-radius: var(--radius-lg);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-sm);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        border: 1px solid var(--border-color);
    }

    .header h1 {
        color: var(--text-primary);
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .header-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .content-card {
        background: white;
        border-radius: var(--radius-lg);
        padding: 2rem;
        box-shadow: var(--shadow-sm);
        margin-bottom: 2rem;
        border: 1px solid var(--border-color);
        max-width: 100%;
        overflow-x: hidden;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .form-control {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 1rem;
        transition: all 0.2s ease;
        background: var(--bg-primary);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.875rem 1.5rem;
        border: none;
        border-radius: var(--radius-md);
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        background: linear-gradient(135deg, var(--primary-color), var(--success-color));
        color: white;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #6b7280, #4b5563);
    }

    .btn-info {
        background: linear-gradient(135deg, var(--accent-color), #2563eb);
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger-color), #dc2626);
    }

    .radio-group {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .radio-option {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        border: 2px solid rgba(16, 185, 129, 0.1);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.8);
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .radio-option:hover {
        background: rgba(16, 185, 129, 0.02);
        border-color: rgba(16, 185, 129, 0.2);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);
    }

    .radio-option input[type="radio"] {
        width: 18px;
        height: 18px;
        accent-color: #10b981;
        cursor: pointer;
    }

    .radio-option label {
        margin: 0;
        font-weight: 500;
        color: var(--text-primary);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex: 1;
    }

    .radio-option input[type="radio"]:checked + label {
        color: #10b981;
        font-weight: 600;
    }

    .radio-option:has(input[type="radio"]:checked) {
        border-color: #10b981;
        background: rgba(16, 185, 129, 0.05);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    .info-box {
        background: rgba(59, 130, 246, 0.05);
        border: 1px solid rgba(59, 130, 246, 0.1);
        border-radius: var(--radius-md);
        padding: 1rem;
        margin-top: 0.75rem;
    }

    .info-box p {
        margin: 0;
        color: #3b82f6;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .error-message {
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    @media (max-width: 1024px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.open {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0;
            padding: 1rem;
        }

        .mobile-menu-btn {
            display: block !important;
        }

        .header {
            padding: 1.5rem;
        }

        .header h1 {
            font-size: 1.5rem;
        }
    }

    /* Enhanced Media Management Styles */
    .current-files {
        background: rgba(248, 250, 252, 0.5);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .current-files h4 {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 1rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .current-files h4 i {
        color: var(--primary-color);
    }

    .file-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        margin-bottom: 0.75rem;
        transition: all 0.2s ease;
    }

    .file-item:hover {
        box-shadow: var(--shadow-sm);
        border-color: var(--primary-color);
    }

    .file-item:last-child {
        margin-bottom: 0;
    }

    .file-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .file-icon {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        color: white;
    }

    .file-icon.image { background: var(--primary-color); }
    .file-icon.video { background: var(--danger-color); }

    .file-details h5 {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 0.25rem 0;
    }

    .file-details p {
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin: 0;
    }

    .file-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .file-actions a {
        padding: 0.375rem 0.75rem;
        border-radius: var(--radius-sm);
        text-decoration: none;
        font-size: 0.75rem;
        font-weight: 500;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .file-actions .view-btn {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
        border: 1px solid rgba(59, 130, 246, 0.2);
    }

    .file-actions a:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    /* Enhanced Checkbox Styling */
    .checkbox-group {
        margin: 1rem 0;
        padding: 1rem;
        border: 2px solid #e5e7eb;
        border-radius: var(--radius-lg);
        background: #f9fafb;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .checkbox-group:hover {
        border-color: #3b82f6;
        background: #eff6ff;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: var(--radius-md);
        transition: all 0.3s ease;
        position: relative;
    }

    .checkbox-label:hover {
        background: rgba(59, 130, 246, 0.05);
    }

    .checkbox-input {
        display: none;
    }

    .checkbox-custom {
        width: 24px;
        height: 24px;
        border: 2px solid #d1d5db;
        border-radius: 6px;
        position: relative;
        transition: all 0.3s ease;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .checkbox-input:checked + .checkbox-custom {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }

    .checkbox-icon {
        color: white;
        font-size: 12px;
        opacity: 0;
        transition: all 0.3s ease;
        transform: scale(0);
    }

    .checkbox-input:checked + .checkbox-custom .checkbox-icon {
        opacity: 1;
        transform: scale(1);
        animation: checkBounce 0.3s ease;
    }

    @keyframes checkBounce {
        0% { transform: scale(0); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }

    .checkbox-text {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        color: #374151;
        transition: color 0.3s ease;
    }

    .checkbox-input:checked ~ .checkbox-text {
        color: #3b82f6;
        font-weight: 600;
    }

    .checkbox-text i {
        color: #3b82f6;
        transition: color 0.3s ease;
    }

    .form-help {
        display: block;
        margin-top: 0.5rem;
        color: #6b7280;
        font-size: 0.875rem;
        font-style: italic;
    }

    /* Enhanced Form Actions */
    .form-group:last-of-type {
        border-top: 1px solid var(--border-color);
        padding-top: 2rem;
        margin-top: 2rem;
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.875rem 1.5rem;
        border: none;
        border-radius: var(--radius-md);
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        position: relative;
        overflow: hidden;
        min-width: 140px;
        justify-content: center;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--success-color));
        color: white;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, var(--success-color), #047857);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger-color), #dc2626);
        color: white;
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
    }

    .btn .btn-shine {
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.6s ease;
    }

    .btn:hover .btn-shine {
        left: 100%;
    }

    .file-upload-area {
        border: 2px dashed var(--border-color);
        border-radius: var(--radius-md);
        padding: 2rem;
        text-align: center;
        background: rgba(248, 250, 252, 0.3);
        transition: all 0.2s ease;
        cursor: pointer;
        margin-bottom: 1rem;
    }

    .file-upload-area:hover {
        border-color: var(--primary-color);
        background: rgba(16, 185, 129, 0.02);
    }

    .file-upload-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }

    .file-upload-content i {
        font-size: 2rem;
        color: var(--primary-color);
    }

    .file-upload-content p {
        margin: 0;
        font-weight: 600;
        color: var(--text-primary);
    }

    .file-upload-content small {
        color: var(--text-secondary);
        font-size: 0.75rem;
    }

    .file-previews {
        margin-top: 1rem;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
    }

    .file-preview-item {
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        overflow: hidden;
        background: white;
        position: relative;
    }

    .preview-image {
        position: relative;
        height: 150px;
        overflow: hidden;
    }

    .preview-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .media-preview {
        width: 100%;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(248, 250, 252, 0.8);
        position: relative;
        overflow: hidden;
    }

    .media-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 0;
    }

    .video-thumbnail {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 0;
    }

    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .media-item {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        overflow: hidden;
        transition: all 0.2s ease;
        position: relative;
    }

    .media-item:hover {
        box-shadow: var(--shadow-md);
        border-color: var(--primary-color);
        transform: translateY(-2px);
    }

    .media-info {
        padding: 0.75rem;
        border-top: 1px solid var(--border-color);
    }

    .media-name {
        margin: 0 0 0.25rem 0;
        font-weight: 500;
        font-size: 0.875rem;
        color: var(--text-primary);
        word-break: break-word;
    }

    .media-size {
        color: var(--text-secondary);
        font-size: 0.75rem;
    }

    .media-actions {
        padding: 0.75rem;
        border-top: 1px solid var(--border-color);
        background: rgba(248, 250, 252, 0.3);
    }

    .remove-checkbox {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        font-size: 0.75rem;
        font-weight: 500;
        color: #ef4444;
        margin: 0;
    }

    .remove-checkbox input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: #ef4444;
    }

    .preview-video {
        position: relative;
        height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: var(--primary-color);
    }

    .preview-video i {
        font-size: 3rem;
    }

    .remove-file-btn {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        width: 24px;
        height: 24px;
        border: none;
        border-radius: 50%;
        background: rgba(239, 68, 68, 0.9);
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        transition: all 0.2s ease;
    }

    .remove-file-btn:hover {
        background: #dc2626;
        transform: scale(1.1);
    }

    .file-preview-info {
        padding: 0.75rem;
        border-top: 1px solid var(--border-color);
    }

    .file-name {
        margin: 0 0 0.25rem 0;
        font-weight: 500;
        font-size: 0.875rem;
        color: var(--text-primary);
        word-break: break-word;
    }

    .file-size {
        color: var(--text-secondary);
        font-size: 0.75rem;
    }

    /* Current Media Grid Styles */
    .current-media-grid {
        margin-top: 1rem;
    }

    .current-media-section {
        margin-bottom: 2rem;
    }

    .current-media-section:last-child {
        margin-bottom: 0;
    }

    .current-media-section h4 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 1rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(16, 185, 129, 0.02));
        border: 1px solid rgba(16, 185, 129, 0.1);
        border-radius: var(--radius-md);
    }

    .current-media-section h4 i {
        color: var(--primary-color);
        font-size: 1.1rem;
    }

    /* Video Container Styles */
    .video-media-item {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        overflow: hidden;
        transition: all 0.2s ease;
        position: relative;
    }

    .video-media-item:hover {
        box-shadow: var(--shadow-md);
        border-color: var(--primary-color);
        transform: translateY(-2px);
    }

    .video-container {
        position: relative;
        width: 100%;
        height: 180px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .current-video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border: none;
        border-radius: 0;
    }

    .video-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0, 0, 0, 0.7);
        border-radius: 50%;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        pointer-events: none;
        transition: all 0.3s ease;
    }

    .video-container:hover .video-overlay {
        background: rgba(0, 0, 0, 0.8);
        transform: translate(-50%, -50%) scale(1.1);
    }

    .video-info {
        padding: 0.75rem;
        border-top: 1px solid var(--border-color);
        background: white;
    }

    .video-filename {
        font-weight: 500;
        font-size: 0.875rem;
        color: var(--text-primary);
        word-break: break-word;
        display: block;
        margin-bottom: 0.25rem;
    }

    .remove-media-checkbox {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 180px; /* Match video container height */
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 10;
        border-radius: var(--radius-md) var(--radius-md) 0 0;
    }

    .video-media-item:hover .remove-media-checkbox {
        opacity: 1;
    }

    .remove-media-checkbox input[type="checkbox"] {
        display: none;
    }

    .remove-media-checkbox label {
        color: white;
        cursor: pointer;
        padding: 0.5rem 1rem;
        background: rgba(239, 68, 68, 0.8);
        border-radius: var(--radius-md);
        font-size: 0.8rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: background 0.2s ease;
    }

    .remove-media-checkbox label:hover {
        background: #ef4444;
    }

    .remove-media-checkbox input[type="checkbox"]:checked + label {
        background: var(--primary-color);
    }

    /* Responsive Grid for Media Items */
    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 1rem;
        align-items: start;
    }

    @media (max-width: 768px) {
        .media-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .video-container {
            height: 160px;
        }
    }

    @media (max-width: 480px) {
        .video-container {
            height: 140px;
        }
        
        .media-grid {
            gap: 0.75rem;
        }
    }

    /* Additional alignment fixes */
    .current-media-grid .media-item {
        max-width: 100%;
        height: auto;
        display: flex;
        flex-direction: column;
    }

    .current-media-grid .media-preview {
        flex-shrink: 0;
    }

    .current-media-grid .video-media-item {
        max-height: 320px;
        overflow: hidden;
    }

    /* Video Preview Styles for Upload Section */
    .video-preview-item {
        background: var(--bg-secondary);
        border: 2px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .video-preview-item:hover {
        border-color: var(--primary-color);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .video-preview-container {
        position: relative;
        width: 100%;
        height: 120px;
        overflow: hidden;
        border-radius: var(--radius-md) var(--radius-md) 0 0;
    }

    .file-preview-video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        background: #000;
    }

    .video-preview-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: rgba(255, 255, 255, 0.9);
        font-size: 2rem;
        pointer-events: none;
        opacity: 0.8;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        transition: opacity 0.3s ease;
    }

    .video-preview-container:hover .video-preview-overlay {
        opacity: 1;
    }

    .file-preview-info {
        padding: 0.75rem;
        background: var(--bg-primary);
        border-top: 1px solid var(--border-color);
    }

    .file-preview-name {
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--text-primary);
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .file-preview-size {
        font-size: 0.7rem;
        color: var(--text-secondary);
        margin-top: 0.25rem;
    }

    .file-preview-remove {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #ef4444;
        color: white;
        border: 2px solid white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.75rem;
        transition: all 0.2s ease;
        z-index: 10;
    }

    .file-preview-remove:hover {
        background: #dc2626;
        transform: scale(1.1);
    }
</style>
@endpush

@section('content')
<div class="dashboard">
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" onclick="toggleSidebar()" style="display: none; position: fixed; top: 1rem; left: 1rem; z-index: 1001; background: var(--primary-color); color: white; border: none; padding: 0.75rem; border-radius: var(--radius-md); box-shadow: var(--shadow-md);">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-user-shield"></i> Department Admin</h3>
            <div class="dept-info">{{ auth('admin')->user()->department }} Department</div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="{{ route('department-admin.dashboard') }}">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a></li>
            <li><a href="{{ route('department-admin.announcements.index') }}">
                <i class="fas fa-bullhorn"></i> Announcements
            </a></li>
            <li><a href="{{ route('department-admin.events.index') }}" class="active">
                <i class="fas fa-calendar-alt"></i> Events
            </a></li>
            <li><a href="{{ route('department-admin.news.index') }}">
                <i class="fas fa-newspaper"></i> News
            </a></li>
            <li>
                
            </li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="header">
            <div>
                <h1><i class="fas fa-edit"></i> Edit Event</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">Update event information</p>
            </div>
            <div class="header-actions">
               
                <a href="{{ route('department-admin.events.index') }}" class="btn btn-info">
                    <i class="fas fa-arrow-left"></i> Back to Events
                </a>
            </div>
        </div>

        <!-- Content Card -->
        <div class="content-card">
            <form method="POST" action="{{ route('department-admin.events.update', $event) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="title">Event Title *</label>
                    <input type="text" id="title" name="title" class="form-control" value="{{ old('title', $event->title) }}" required>
                </div>

                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" class="form-control" rows="5" required>{{ old('description', $event->description) }}</textarea>
                </div>

                <div class="form-group">
                    <label for="event_date">Event Date & Time *</label>
                    <input type="datetime-local" id="event_date" name="event_date" class="form-control" value="{{ old('event_date', $event->event_date ? $event->event_date->format('Y-m-d\TH:i') : '') }}" required>
                </div>

                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" class="form-control" value="{{ old('location', $event->location) }}">
                </div>

                <!-- Current Media Files Display -->
                @if($event->allImagePaths || $event->allVideoPaths)
                    <div class="form-group">
                        <label>
                            <i class="fas fa-folder-open"></i>
                            Current Media Files
                        </label>
                        <div class="current-media-grid">

                            <!-- Current Images Section -->
                            @if($event->allImagePaths && count($event->allImagePaths) > 0)
                                <div class="current-media-section">
                                    <h4><i class="fas fa-images"></i> Current Images ({{ count($event->allImagePaths) }})</h4>
                                    <div class="media-grid">
                                        @foreach($event->allImagePaths as $index => $imagePath)
                                            @if($imagePath)
                                                <div class="media-item">
                                                    <div class="media-preview">
                                                        <img src="{{ asset('storage/' . $imagePath) }}" alt="Image {{ $index + 1 }}">
                                                    </div>
                                                    <div class="media-info">
                                                        <p class="media-name">{{ basename($imagePath) }}</p>
                                                        <small class="media-size">{{ number_format(Storage::disk('public')->size($imagePath) / 1024, 1) }} KB</small>
                                                    </div>
                                                    <div class="media-actions">
                                                        <label class="remove-checkbox">
                                                            <input type="checkbox" name="remove_images[]" value="{{ $index }}">
                                                            <span>Remove</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Current Videos Section -->
                            @if($event->allVideoPaths && count($event->allVideoPaths) > 0)
                                <div class="current-media-section">
                                    <h4><i class="fas fa-video"></i> Current Videos ({{ count($event->allVideoPaths) }})</h4>
                                    <div class="media-grid">
                                        @foreach($event->allVideoPaths as $index => $videoPath)
                                            @if($videoPath)
                                                <div class="media-item video-media-item">
                                                    <div class="video-container">
                                                        <video controls preload="metadata" class="current-video">
                                                            <source src="{{ asset('storage/' . $videoPath) }}" type="video/mp4">
                                                            Your browser does not support the video tag.
                                                        </video>
                                                        <div class="video-overlay">
                                                            <i class="fas fa-play-circle"></i>
                                                        </div>
                                                    </div>
                                                    <div class="video-info">
                                                        <span class="video-filename">{{ basename($videoPath) }}</span>
                                                    </div>
                                                    <div class="remove-media-checkbox">
                                                        <input type="checkbox" name="remove_videos[]" value="{{ $index }}" id="remove_video_{{ $index }}">
                                                        <label for="remove_video_{{ $index }}"><i class="fas fa-trash-alt"></i> Remove</label>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                @endif

                <!-- Form Group: Multiple Images Upload -->
                <div class="form-group">
                    <label for="images">
                        <i class="fas fa-images"></i>
                        Upload Images (Optional - Max 2 images)
                    </label>
                    <div class="file-upload-area" onclick="document.getElementById('images').click()">
                        <input type="file"
                               id="images"
                               name="images[]"
                               class="file-input @error('images') error @enderror"
                               accept=".jpg,.jpeg,.png"
                               multiple
                               style="display: none;">
                        <div class="file-upload-content">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Click to upload or drag and drop</p>
                            <small>JPG, PNG up to 5MB each (Max 2 images)</small>
                        </div>
                    </div>
                    <div id="imagesPreview" class="file-previews"></div>
                    @error('images')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                    @error('images.*')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Form Group: Multiple Videos Upload -->
                <div class="form-group">
                    <label for="videos">
                        <i class="fas fa-video"></i>
                        Upload Videos (Optional - Max 1 video)
                    </label>
                    <div class="file-upload-area" onclick="document.getElementById('videos').click()">
                        <input type="file"
                               id="videos"
                               name="videos[]"
                               class="file-input @error('videos') error @enderror"
                               accept=".mp4"
                               multiple
                               style="display: none;">
                        <div class="file-upload-content">
                            <i class="fas fa-video"></i>
                            <p>Click to upload or drag and drop</p>
                            <small>MP4 up to 50MB each (Max 1 video)</small>
                        </div>
                    </div>
                    <div id="videosPreview" class="file-previews"></div>
                    @error('videos')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                    @error('videos.*')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Form Group: Visibility Settings -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-eye"></i>
                        Visibility
                    </label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" 
                                   id="visibility_department" 
                                   name="visibility_scope" 
                                   value="department" 
                                   {{ old('visibility_scope', $event->visibility_scope ?? 'department') == 'department' ? 'checked' : '' }}>
                            <label for="visibility_department">
                                <i class="fas fa-users"></i>
                                {{ auth('admin')->user()->department }} Department (only your students)
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" 
                                   id="visibility_all" 
                                   name="visibility_scope" 
                                   value="all" 
                                   {{ old('visibility_scope', $event->visibility_scope ?? 'department') == 'all' ? 'checked' : '' }}>
                            <label for="visibility_all">
                                <i class="fas fa-globe"></i>
                                All Departments (will show "Posted by {{ auth('admin')->user()->department }} Department")
                            </label>
                        </div>
                    </div>
                    @error('visibility_scope')
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                    <div class="info-box">
                        <p>
                            <i class="fas fa-info-circle"></i>
                            Choose who can see this event. Department-only posts are visible to your students, while All Departments posts are visible to everyone with proper attribution.
                        </p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-eye"></i> Publish Event
                    </label>
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox"
                                   name="is_published"
                                   value="1"
                                   {{ old('is_published', $event->is_published) ? 'checked' : '' }}
                                   class="checkbox-input">
                            <span class="checkbox-custom">
                                <i class="fas fa-check checkbox-icon"></i>
                            </span>
                            <span class="checkbox-text">
                                <i class="fas fa-eye"></i> Publish Event
                            </span>
                        </label>
                        <small class="form-help">Check to make this event visible to students and faculty immediately</small>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Event
                        <div class="btn-shine"></div>
                    </button>
                    <a href="{{ route('department-admin.events.index') }}" class="btn btn-danger">
                        <i class="fas fa-times"></i> Cancel
                        <div class="btn-shine"></div>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Multiple Images Upload Handler
    const imagesInput = document.getElementById('images');
    const imagesPreview = document.getElementById('imagesPreview');
    let selectedImages = [];
    const maxImages = 2;

    if (imagesInput) {
        imagesInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            
            // Limit to 2 images
            if (files.length > maxImages) {
                Swal.fire({
                    icon: 'error',
                    title: 'Upload Limit Exceeded',
                    text: `You can only upload up to ${maxImages} images.`,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'OK'
                });
                this.value = '';
                return;
            }
            
            selectedImages = files;
            displayImagePreviews();
        });
    }

    function displayImagePreviews() {
        imagesPreview.innerHTML = '';
        
        selectedImages.forEach((file, index) => {
            // Validate file type
            if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid File Type',
                    text: `File ${file.name} is not a valid image format. Only JPG and PNG are allowed.`,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Too Large',
                    text: `File ${file.name} is too large. Maximum size is 5MB.`,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            const previewItem = document.createElement('div');
            previewItem.className = 'file-preview-item';
            
            const reader = new FileReader();
            reader.onload = function(e) {
                previewItem.innerHTML = `
                    <div class="preview-image">
                        <img src="${e.target.result}" alt="Preview ${index + 1}">
                        <button type="button" class="remove-file-btn" onclick="removeImage(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="file-preview-info">
                        <p class="file-name">${file.name}</p>
                        <small class="file-size">${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
            
            imagesPreview.appendChild(previewItem);
        });
    }

    // Multiple Videos Upload Handler
    const videosInput = document.getElementById('videos');
    const videosPreview = document.getElementById('videosPreview');
    let selectedVideos = [];
    const maxVideos = 1;

    if (videosInput) {
        videosInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            
            // Limit to 1 video
            if (files.length > maxVideos) {
                Swal.fire({
                    icon: 'error',
                    title: 'Upload Limit Exceeded',
                    text: `You can only upload up to ${maxVideos} videos.`,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'OK'
                });
                this.value = '';
                return;
            }
            
            selectedVideos = files;
            displayVideoPreviews();
        });
    }

    function displayVideoPreviews() {
        videosPreview.innerHTML = '';
        
        selectedVideos.forEach((file, index) => {
            // Validate file type
            if (file.type !== 'video/mp4') {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid File Type',
                    text: `File ${file.name} is not a valid video format. Only MP4 is allowed.`,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Validate file size (50MB)
            if (file.size > 50 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Too Large',
                    text: `File ${file.name} is too large. Maximum size is 50MB.`,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Create preview with actual video thumbnail
            const previewItem = document.createElement('div');
            previewItem.className = 'file-preview-item video-preview-item';
            
            // Create video container
            const videoContainer = document.createElement('div');
            videoContainer.className = 'video-preview-container';
            
            // Create video element for thumbnail
            const videoElement = document.createElement('video');
            videoElement.className = 'file-preview-video';
            videoElement.controls = true;
            videoElement.preload = 'metadata';
            
            // Create object URL for the video file
            const videoURL = URL.createObjectURL(file);
            videoElement.src = videoURL;
            
            // Add play overlay
            const playOverlay = document.createElement('div');
            playOverlay.className = 'video-preview-overlay';
            playOverlay.innerHTML = '<i class="fas fa-play-circle"></i>';
            
            // Add remove button
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'file-preview-remove';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.onclick = function() {
                // Revoke object URL to free memory
                URL.revokeObjectURL(videoURL);
                removeVideo(index);
            };
            
            videoContainer.appendChild(videoElement);
            videoContainer.appendChild(playOverlay);
            
            const fileInfo = document.createElement('div');
            fileInfo.className = 'file-preview-info';
            fileInfo.innerHTML = `
                <span class="file-preview-name">${file.name}</span>
                <span class="file-preview-size">${(file.size / 1024 / 1024).toFixed(1)} MB</span>
            `;
            
            previewItem.appendChild(videoContainer);
            previewItem.appendChild(fileInfo);
            previewItem.appendChild(removeBtn);
            videosPreview.appendChild(previewItem);
        });
    }

    // Global functions for removing files
    window.removeImage = function(index) {
        selectedImages.splice(index, 1);
        updateImageInput();
        displayImagePreviews();
    };

    window.removeVideo = function(index) {
        selectedVideos.splice(index, 1);
        updateVideoInput();
        displayVideoPreviews();
    };

    function updateImageInput() {
        const dt = new DataTransfer();
        selectedImages.forEach(file => dt.items.add(file));
        imagesInput.files = dt.files;
    }

    function updateVideoInput() {
        const dt = new DataTransfer();
        selectedVideos.forEach(file => dt.items.add(file));
        videosInput.files = dt.files;
    }

    // Form validation enhancement
    const form = document.querySelector('form');
    const submitBtn = document.querySelector('.btn');

    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();
            const eventDate = document.getElementById('event_date').value.trim();

            if (!title || !description || !eventDate) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }

            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            submitBtn.disabled = true;
        });
    }

    // Auto-resize textarea
    const textarea = document.querySelector('textarea');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.max(120, this.scrollHeight) + 'px';
        });

        // Initial resize
        textarea.style.height = Math.max(120, textarea.scrollHeight) + 'px';
    }

    // Mobile sidebar toggle
    window.toggleSidebar = function() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('open');
    };

    // Handle logout
    window.handleLogout = function() {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You will be logged out of your account.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, logout',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Logging out...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                document.getElementById('logout-form').submit();
            }
        });
    };

    // Responsive sidebar
    window.addEventListener('resize', function() {
        const sidebar = document.querySelector('.sidebar');
        if (window.innerWidth > 1024) {
            sidebar.classList.remove('open');
        }
    });
});
</script>
@endpush
