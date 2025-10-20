@php
use Illuminate\Support\Facades\Storage;
@endphp

@extends('layouts.app')

@section('title', 'Edit Announcement - Department Admin')

@push('styles')
<style>
    :root {
        --primary-color: #000000;
        --secondary-color: #ffffff;
        --text-primary: #1f2937;
        --text-secondary: #6b7280;
        --border-color: #e5e7eb;
        --radius-sm: 6px;
        --radius-md: 8px;
        --radius-lg: 12px;
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
        --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background: white;
        color: var(--text-primary);
        margin: 0;
        padding: 0;
    }

    .dashboard {
        display: flex;
        min-height: 100vh;
        background: white;
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
        gap: 0.5rem;
        background: linear-gradient(135deg, #ffffff 0%, #e5e7eb 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        position: relative;
        margin: 0;
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
        padding: 1rem 0;
        margin: 0;
    }

    .sidebar-menu li {
        margin: 0.5rem 0;
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        padding: 1rem 2rem;
        color: #d1d5db;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        gap: 1rem;
        position: relative;
        border-radius: 0 25px 25px 0;
        margin: 0.25rem 0;
        overflow: hidden;
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
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
    }

    .sidebar-menu a:hover i,
    .sidebar-menu a.active i {
        transform: scale(1.2) rotate(5deg);
        color: #ffffff;
    }

    .sidebar-menu a span {
        transition: all 0.3s ease;
    }

    .sidebar-menu a:hover span,
    .sidebar-menu a.active span {
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .main-content {
        flex: 1;
        margin-left: 280px;
        background: white;
        min-height: 100vh;
        padding: 2rem;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid var(--border-color);
    }

    .header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0 0 0.5rem 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .header h1 i {
        color: #10b981;
        background: rgba(16, 185, 129, 0.1);
        padding: 0.5rem;
        border-radius: 8px;
    }

    .header p {
        margin: 0;
        color: var(--text-secondary);
        font-size: 0.875rem;
    }

    .header-actions {
        display: flex;
        gap: 1rem;
    }

    .form-container {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        border: 1px solid var(--border-color);
        position: relative;
    }

    .form-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .announcement-info {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        padding: 2rem;
        border-bottom: 1px solid var(--border-color);
        background: white;
    }

    .info-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #10b981, #059669);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .info-content h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0 0 0.75rem 0;
    }

    .info-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        font-size: 0.875rem;
        color: var(--text-secondary);
    }

    .info-meta span {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .info-meta i {
        color: #10b981;
    }

    .separator {
        color: var(--border-color);
        font-weight: bold;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-badge.published {
        background: rgba(34, 197, 94, 0.1);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.2);
    }

    .status-badge.draft {
        background: rgba(251, 191, 36, 0.1);
        color: #fbbf24;
        border: 1px solid rgba(251, 191, 36, 0.2);
    }

    .department-badge {
        background: rgba(99, 102, 241, 0.1);
        color: #6366f1;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid rgba(99, 102, 241, 0.2);
    }

    .form-header {
        padding: 2rem 2rem 1rem 2rem;
        border-bottom: 1px solid var(--border-color);
        background: white;
    }

    .form-header h2 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0 0 0.5rem 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .form-header h2 i {
        color: #10b981;
    }

    .form-header p {
        margin: 0;
        color: var(--text-secondary);
        font-size: 0.875rem;
    }

    .form-body {
        padding: 2rem;
    }

    .form-group {
        margin-bottom: 2rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.75rem;
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-group label i {
        color: #10b981;
    }

    .form-control {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        transition: all 0.2s ease;
        background: white;
    }

    .form-control:focus {
        outline: none;
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    .form-control.error {
        border-color: #ef4444;
    }

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
        font-family: inherit;
        line-height: 1.5;
    }

    .error-message {
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

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
        color: #10b981;
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
        border-color: #10b981;
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

    .file-icon.image { background: #10b981; }
    .file-icon.video { background: #ef4444; }
    .file-preview-item {
        margin-bottom: 0.75rem;
        padding: 1rem;
        background: rgba(248, 250, 252, 0.5);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
    }

    .preview-content {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .preview-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .file-name {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.875rem;
    }

    .file-size {
        color: var(--text-secondary);
        font-size: 0.75rem;
    }

    .remove-file {
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.75rem;
        transition: all 0.2s ease;
    }

    .remove-file:hover {
        background: #dc2626;
        transform: scale(1.1);
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
        border-color: #10b981;
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
        color: #10b981;
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
    }

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

    .file-actions .download-btn {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .file-actions a:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .file-upload-section {
        border: 2px dashed var(--border-color);
        border-radius: var(--radius-md);
        padding: 2rem;
        text-align: center;
        background: rgba(248, 250, 252, 0.3);
        transition: all 0.2s ease;
        cursor: pointer;
        position: relative;
    }

    .file-upload-section:hover {
        border-color: #10b981;
        background: rgba(16, 185, 129, 0.02);
    }

    .file-upload-section.dragover {
        border-color: #10b981;
        background: rgba(16, 185, 129, 0.05);
        transform: scale(1.01);
    }

    .upload-icon {
        font-size: 2rem;
        color: #10b981;
        margin-bottom: 1rem;
    }

    .upload-text {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .upload-hint {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .remove-file-option {
        margin-top: 1rem;
        padding: 1rem;
        background: rgba(239, 68, 68, 0.05);
        border: 1px solid rgba(239, 68, 68, 0.1);
        border-radius: var(--radius-md);
    }

    .remove-file-option label {
        color: #ef4444 !important;
        font-weight: 500 !important;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        background: rgba(248, 250, 252, 0.5);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        transition: all 0.2s ease;
    }

    .checkbox-group:hover {
        background: rgba(16, 185, 129, 0.02);
        border-color: rgba(16, 185, 129, 0.2);
    }

    .checkbox-group input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #10b981;
    }

    .checkbox-group label {
        margin: 0;
        font-weight: 500;
        color: var(--text-primary);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
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
        font-size: 0.75rem;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .info-box i {
        color: #3b82f6;
    }

    .file-actions .remove-btn {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.2);
        padding: 0.375rem 0.75rem;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .file-actions .remove-btn:hover {
        background: rgba(239, 68, 68, 0.2);
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
    }

    .restore-btn {
        background: rgba(34, 197, 94, 0.1);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.2);
        padding: 0.375rem 0.75rem;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .restore-btn:hover {
        background: rgba(34, 197, 94, 0.2);
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(34, 197, 94, 0.2);
    }

    .removed-indicator {
        margin-top: 0.5rem;
        padding: 0.5rem;
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.2);
        border-radius: var(--radius-sm);
        color: #ef4444;
        font-size: 0.75rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .removed-indicator i {
        color: #ef4444;
    }

    /* Enhanced Media Management Styles */
    .current-media-grid {
        margin-bottom: 1.5rem;
        padding: 1.5rem;
        background: rgba(248, 250, 252, 0.5);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
    }

    .current-media-grid h4 {
        margin: 0 0 1rem 0;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .current-media-grid h4 i {
        color: #10b981;
    }

    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
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
        border-color: #10b981;
        transform: translateY(-2px);
    }

    /* Enhanced Video Styling */
    .video-media-item {
        background: var(--bg-secondary);
        border: 2px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .video-media-item:hover {
        border-color: #10b981;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .video-container {
        position: relative;
        width: 100%;
        height: 120px;
        overflow: hidden;
        border-radius: var(--radius-md) var(--radius-md) 0 0;
    }

    .current-video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        background: #000;
    }

    .video-overlay {
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

    .video-container:hover .video-overlay {
        opacity: 1;
    }

    .video-info {
        padding: 0.75rem;
        background: var(--bg-primary);
        border-top: 1px solid var(--border-color);
    }

    .video-filename {
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--text-secondary);
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .remove-media-checkbox {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 120px; /* Match video container height */
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 10;
    }

    .video-media-item:hover .remove-media-checkbox {
        opacity: 1;
    }

    /* Special positioning for video items */
    .video-media-item .remove-media-checkbox {
        border-radius: var(--radius-md) var(--radius-md) 0 0;
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
        background: #10b981;
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

    .media-preview.video-preview {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
    }

    .media-preview.video-preview i {
        font-size: 2rem;
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

    .file-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .file-preview-item {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        overflow: hidden;
        position: relative;
        transition: all 0.2s ease;
    }

    .file-preview-item:hover {
        box-shadow: var(--shadow-md);
        border-color: #10b981;
        transform: translateY(-2px);
    }

    /* Video Preview Styles */
    .video-preview-item {
        background: var(--bg-secondary);
        border: 2px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .video-preview-item:hover {
        border-color: #10b981;
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

    .preview-image {
        width: 100%;
        height: 120px;
        position: relative;
        overflow: hidden;
        background: rgba(248, 250, 252, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .preview-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .preview-video {
        width: 100%;
        height: 120px;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .preview-video i {
        font-size: 2rem;
    }

    .remove-file-btn {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: none;
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

    .file-info {
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

    .file-upload-content p {
        margin: 0 0 0.5rem 0;
        font-weight: 500;
        color: var(--text-primary);
    }

    .file-upload-content small {
        color: var(--text-secondary);
    }

    .file-preview {
        text-align: center;
        padding: 1.5rem;
        background: rgba(16, 185, 129, 0.05);
        border: 1px solid rgba(16, 185, 129, 0.1);
        border-radius: var(--radius-md);
        margin-top: 1rem;
    }

    .file-upload-area.dragover {
        border-color: #10b981;
        background: rgba(16, 185, 129, 0.05);
        transform: scale(1.01);
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        padding: 2rem;
        border-top: 1px solid var(--border-color);
        background: white;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
    }

    .btn-primary {
        background: #10b981;
        color: white;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .btn-primary:hover {
        background: #059669;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }

    .btn-secondary {
        background: white;
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
    }

    .btn-danger {
        background: #ef4444;
        color: white;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .btn-danger:hover {
        background: #dc2626;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }

    .btn-info {
        background: #3b82f6;
        color: white;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .btn-info:hover {
        background: #2563eb;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }

    @media (max-width: 768px) {
        .header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .header-actions {
            width: 100%;
            justify-content: center;
        }

        .form-actions {
            flex-direction: column;
        }

        .file-item {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .file-actions {
            justify-content: center;
        }
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
            <li><a href="{{ route('department-admin.announcements.index') }}" class="active">
                <i class="fas fa-bullhorn"></i> Announcements
            </a></li>
            <li><a href="{{ route('department-admin.events.index') }}">
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
        <!-- Header Section -->
        <div class="header">
            <div>
                <h1>
                    <i class="fas fa-edit"></i>
                    Edit Announcement
                </h1>
                <p>Update announcement details and manage attachments</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('department-admin.announcements.index') }}" class="btn btn-info">
                    <i class="fas fa-arrow-left"></i>
                    Back to Announcements
                </a>
            </div>
        </div>

<!-- Form Container -->
<div class="form-container">
    <!-- Announcement Info Header -->
    <div class="announcement-info">
        <div class="info-icon">
            <i class="fas fa-bullhorn"></i>
        </div>
        <div class="info-content">
            <h2>{{ $announcement->title }}</h2>
            <div class="info-meta">
                <span>
                    <i class="fas fa-calendar"></i>
                    Created {{ $announcement->created_at->format('M d, Y') }}
                </span>
                <span class="separator">•</span>
                <span>
                    <i class="fas fa-user"></i>
                    {{ $announcement->admin->name }}
                </span>
                <span class="separator">•</span>
                <span class="status-badge {{ $announcement->is_published ? 'published' : 'draft' }}">
                    <i class="fas fa-{{ $announcement->is_published ? 'check-circle' : 'clock' }}"></i>
                    {{ $announcement->is_published ? 'Published' : 'Draft' }}
                </span>
                <span class="separator">•</span>
                <span class="department-badge">
                    <i class="fas fa-building"></i>
                    {{ $announcement->admin->department ?? 'Department' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Form Header -->
    <div class="form-header">
        <h2>
            <i class="fas fa-edit"></i>
            Edit Announcement Details
        </h2>
        <p>Update the announcement information and manage file attachments</p>
    </div>

    <form action="{{ route('department-admin.announcements.update', $announcement) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-body">
            <!-- Form Group: Title -->
            <div class="form-group">
                <label for="title">
                    <i class="fas fa-heading"></i>
                    Announcement Title
                </label>
                <input type="text"
                       id="title"
                       name="title"
                       class="form-control @error('title') error @enderror"
                       value="{{ old('title', $announcement->title) }}"
                       placeholder="Enter announcement title"
                       required>
                @error('title')
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Form Group: Content -->
            <div class="form-group">
                <label for="content">
                    <i class="fas fa-align-left"></i>
                    Announcement Content
                </label>
                <textarea id="content"
                          name="content"
                          class="form-control @error('content') error @enderror"
                          placeholder="Enter announcement content"
                          required>{{ old('content', $announcement->content) }}</textarea>
                @error('content')
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Current Media Display -->
            @if($announcement->image_path || $announcement->video_path || (isset($announcement->image_paths) && is_array($announcement->image_paths) && count($announcement->image_paths)) || (isset($announcement->video_paths) && is_array($announcement->video_paths) && count($announcement->video_paths)))
                <div class="form-group">
                    <label>
                        <i class="fas fa-paperclip"></i>
                        Current Media Files
                    </label>
                    
                    <!-- Current Single Image -->
                    @if($announcement->image_path && Storage::disk('public')->exists($announcement->image_path))
                        <div class="current-media-grid">
                            <h4><i class="fas fa-image"></i> Current Image</h4>
                            <div class="media-grid">
                                <div class="media-item">
                                    <div class="media-preview">
                                        <img src="{{ asset('storage/' . $announcement->image_path) }}" alt="Current image">
                                    </div>
                                    <div class="media-info">
                                        <p class="media-name">{{ basename($announcement->image_path) }}</p>
                                        <small class="media-size">{{ number_format(Storage::disk('public')->size($announcement->image_path) / 1024, 1) }} KB</small>
                                    </div>
                                    <div class="media-actions">
                                        <label class="remove-checkbox">
                                            <input type="checkbox" name="remove_image" value="1">
                                            <span>Remove</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Current Images Section -->
                    @if($announcement->allImagePaths && count($announcement->allImagePaths) > 0)
                        <div class="current-media-section">
                            <h4><i class="fas fa-images"></i> Current Images ({{ count($announcement->allImagePaths) }})</h4>
                            <div class="media-grid">
                                @foreach($announcement->allImagePaths as $index => $imagePath)
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

                    <!-- Current Single Video -->
                    @if($announcement->video_path && Storage::disk('public')->exists($announcement->video_path))
                        <div class="current-media-grid">
                            <h4><i class="fas fa-video"></i> Current Video</h4>
                            <div class="media-grid">
                                <div class="media-item">
                                    <div class="media-preview video-preview">
                                        <i class="fas fa-video"></i>
                                    </div>
                                    <div class="media-info">
                                        <p class="media-name">{{ basename($announcement->video_path) }}</p>
                                        <small class="media-size">{{ number_format(Storage::disk('public')->size($announcement->video_path) / 1024 / 1024, 1) }} MB</small>
                                    </div>
                                    <div class="media-actions">
                                        <label class="remove-checkbox">
                                            <input type="checkbox" name="remove_video" value="1">
                                            <span>Remove</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Current Multiple Videos -->
                    @if(isset($announcement->video_paths) && is_array($announcement->video_paths) && count($announcement->video_paths))
                        <div class="current-media-grid">
                            <h4><i class="fas fa-video"></i> Current Videos ({{ count($announcement->video_paths) }})</h4>
                            <div class="media-grid">
                                @foreach($announcement->video_paths as $index => $videoPath)
                                    @if(Storage::disk('public')->exists($videoPath))
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
            @endif

            <!-- Form Group: Multiple Images Upload -->
            <div class="form-group">
                <label for="images">
                    <i class="fas fa-images"></i>
                    Upload New Images (Optional - Max 2 images)
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
                <div id="imagePreviewContainer" class="file-preview-grid"></div>
                @error('images')
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </div>
                @enderror
                @error('images.*')
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Form Group: Multiple Videos Upload -->
            <div class="form-group">
                <label for="videos">
                    <i class="fas fa-video"></i>
                    Upload New Video (Optional - Max 1 video)
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
                <div id="videoPreviewContainer" class="file-preview-grid"></div>
                @error('videos')
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </div>
                @enderror
                @error('videos.*')
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </div>
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
                               {{ old('visibility_scope', $announcement->visibility_scope ?? 'department') == 'department' ? 'checked' : '' }}>
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
                               {{ old('visibility_scope', $announcement->visibility_scope ?? 'department') == 'all' ? 'checked' : '' }}>
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
                        Choose who can see this announcement. Department-only posts are visible to your students, while All Departments posts are visible to everyone with proper attribution.
                    </p>
                </div>
            </div>

            <!-- Form Group: Publication Settings -->
            <div class="form-group">
                <label>
                    <i class="fas fa-globe"></i>
                    Publication Settings
                </label>
                <div class="checkbox-group">
                    <input type="checkbox"
                           id="is_published"
                           name="is_published"
                           value="1"
                           {{ old('is_published', $announcement->is_published) ? 'checked' : '' }}>
                    <label for="is_published">
                        <i class="fas fa-rocket"></i>
                        Publish immediately
                    </label>
                </div>
                <div class="info-box">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        If unchecked, the announcement will be saved as a draft and won't be visible to users until published
                    </p>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <a href="{{ route('department-admin.announcements.index') }}" class="btn btn-danger">
                <i class="fas fa-times"></i>
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Update Announcement
            </button>
        </div>
    </form>
</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Track removed media for replacement logic
    let removedImageIndexes = [];
    let removedVideoIndexes = [];
    let removedSingleImage = false;
    let removedSingleVideo = false;
    
    // Multiple Images Upload Preview
    let selectedImages = [];
    const maxImages = 2;
    
    // Track removed media checkboxes
    document.addEventListener('change', function(e) {
        if (e.target.name === 'remove_images[]') {
            const index = parseInt(e.target.value);
            if (e.target.checked) {
                if (!removedImageIndexes.includes(index)) {
                    removedImageIndexes.push(index);
                }
            } else {
                removedImageIndexes = removedImageIndexes.filter(i => i !== index);
            }
            updateMediaLimits();
        }
        
        if (e.target.name === 'remove_videos[]') {
            const index = parseInt(e.target.value);
            if (e.target.checked) {
                if (!removedVideoIndexes.includes(index)) {
                    removedVideoIndexes.push(index);
                }
            } else {
                removedVideoIndexes = removedVideoIndexes.filter(i => i !== index);
            }
            updateMediaLimits();
        }
        
        if (e.target.name === 'remove_image') {
            removedSingleImage = e.target.checked;
            updateMediaLimits();
        }
        
        if (e.target.name === 'remove_video') {
            removedSingleVideo = e.target.checked;
            updateMediaLimits();
        }
    });
    
    function updateMediaLimits() {
        // Calculate available slots for new media
        const currentImageCount = {{ count($announcement->allImagePaths) }};
        const currentVideoCount = {{ count($announcement->allVideoPaths) }};
        
        const removedImagesCount = removedImageIndexes.length + (removedSingleImage ? 1 : 0);
        const removedVideosCount = removedVideoIndexes.length + (removedSingleVideo ? 1 : 0);
        
        const availableImageSlots = Math.min(maxImages, currentImageCount - removedImagesCount + maxImages);
        const availableVideoSlots = Math.min(maxVideos, currentVideoCount - removedVideosCount + maxVideos);
        
        // Update upload area labels
        const imageLabel = document.querySelector('label[for="images"]');
        const videoLabel = document.querySelector('label[for="videos"]');
        
        if (imageLabel) {
            imageLabel.innerHTML = `<i class="fas fa-images"></i> Upload Images (Max: ${availableImageSlots} available)`;
        }
        
        if (videoLabel) {
            videoLabel.innerHTML = `<i class="fas fa-video"></i> Upload Videos (Max: ${availableVideoSlots} available)`;
        }
    }
    
    document.getElementById('images').addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        const container = document.getElementById('imagePreviewContainer');
        
        // Calculate available slots
        const currentImageCount = {{ count($announcement->allImagePaths) }};
        const removedImagesCount = removedImageIndexes.length + (removedSingleImage ? 1 : 0);
        const availableSlots = Math.min(maxImages, currentImageCount - removedImagesCount + maxImages);
        
        // Validate file count against available slots
        if (files.length > availableSlots) {
            Swal.fire({
                icon: 'error',
                title: 'Upload Limit Exceeded',
                text: `Maximum ${availableSlots} images allowed. You have ${removedImagesCount} removed images that can be replaced.`,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'OK'
            });
            e.target.value = '';
            return;
        }
        
        // Clear previous previews
        container.innerHTML = '';
        selectedImages = [];
        
        files.forEach((file, index) => {
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
            
            selectedImages.push(file);
            
            const reader = new FileReader();
            reader.onload = function(readerEvent) {
                const previewDiv = document.createElement('div');
                previewDiv.className = 'file-preview-item';
                previewDiv.innerHTML = `
                    <div class="preview-image">
                        <img src="${readerEvent.target.result}" alt="Preview ${index + 1}">
                        <button type="button" class="remove-file-btn" onclick="removeImagePreview(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="file-info">
                        <p class="file-name">${file.name}</p>
                        <small class="file-size">${(file.size / (1024 * 1024)).toFixed(2)} MB</small>
                    </div>
                `;
                container.appendChild(previewDiv);
            };
            reader.readAsDataURL(file);
        });
    });
    
    function removeImagePreview(index) {
        const container = document.getElementById('imagePreviewContainer');
        const input = document.getElementById('images');
        
        // Remove from selected files array
        selectedImages.splice(index, 1);
        
        // Update file input
        const dt = new DataTransfer();
        selectedImages.forEach(file => dt.items.add(file));
        input.files = dt.files;
        
        // Refresh preview
        container.innerHTML = '';
        selectedImages.forEach((file, newIndex) => {
            const reader = new FileReader();
            reader.onload = function(readerEvent) {
                const previewDiv = document.createElement('div');
                previewDiv.className = 'file-preview-item';
                previewDiv.innerHTML = `
                    <div class="preview-image">
                        <img src="${readerEvent.target.result}" alt="Preview ${newIndex + 1}">
                        <button type="button" class="remove-file-btn" onclick="removeImagePreview(${newIndex})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="file-info">
                        <p class="file-name">${file.name}</p>
                        <small class="file-size">${(file.size / (1024 * 1024)).toFixed(2)} MB</small>
                    </div>
                `;
                container.appendChild(previewDiv);
            };
            reader.readAsDataURL(file);
        });
    }
    
    // Multiple Videos Upload Preview
    let selectedVideos = [];
    const maxVideos = 1;
    
    document.getElementById('videos').addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        const container = document.getElementById('videoPreviewContainer');
        
        // Calculate available slots
        const currentVideoCount = {{ count($announcement->allVideoPaths) }};
        const removedVideosCount = removedVideoIndexes.length + (removedSingleVideo ? 1 : 0);
        const availableSlots = Math.min(maxVideos, currentVideoCount - removedVideosCount + maxVideos);
        
        // Validate file count against available slots
        if (files.length > availableSlots) {
            Swal.fire({
                icon: 'error',
                title: 'Upload Limit Exceeded',
                text: `Maximum ${availableSlots} video allowed. You have ${removedVideosCount} removed videos that can be replaced.`,
                confirmButtonText: 'OK',
                confirmButtonColor: '#ef4444'
            });
            e.target.value = '';
            return;
        }
        
        // Clear previous previews
        container.innerHTML = '';
        selectedVideos = [];
        
        files.forEach((file, index) => {
            // Validate file type
            if (file.type !== 'video/mp4') {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid File Type',
                    text: `File ${file.name} is not a valid video format. Only MP4 is allowed.`,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#ef4444'
                });
                return;
            }
            
            // Validate file size (50MB)
            if (file.size > 50 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Too Large',
                    text: `File ${file.name} is too large. Maximum size is 50MB.`,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#ef4444'
                });
                return;
            }
            
            selectedVideos.push(file);
            
            // Create preview with actual video thumbnail
            const previewDiv = document.createElement('div');
            previewDiv.className = 'file-preview-item video-preview-item';
            
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
                removeVideoPreview(index);
            };
            
            videoContainer.appendChild(videoElement);
            videoContainer.appendChild(playOverlay);
            
            const fileInfo = document.createElement('div');
            fileInfo.className = 'file-preview-info';
            fileInfo.innerHTML = `
                <span class="file-preview-name">${file.name}</span>
                <span class="file-preview-size">${(file.size / 1024 / 1024).toFixed(1)} MB</span>
            `;
            
            previewDiv.appendChild(videoContainer);
            previewDiv.appendChild(fileInfo);
            previewDiv.appendChild(removeBtn);
            container.appendChild(previewDiv);
        });
    });
    
    function removeVideoPreview(index) {
        const container = document.getElementById('videoPreviewContainer');
        const input = document.getElementById('videos');
        
        // Remove from selected files array
        selectedVideos.splice(index, 1);
        
        // Update file input
        const dt = new DataTransfer();
        selectedVideos.forEach(file => dt.items.add(file));
        input.files = dt.files;
        
        // Refresh preview with enhanced video thumbnails
        container.innerHTML = '';
        selectedVideos.forEach((file, newIndex) => {
            const previewDiv = document.createElement('div');
            previewDiv.className = 'file-preview-item video-preview-item';
            
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
                removeVideoPreview(newIndex);
            };
            
            videoContainer.appendChild(videoElement);
            videoContainer.appendChild(playOverlay);
            
            const fileInfo = document.createElement('div');
            fileInfo.className = 'file-preview-info';
            fileInfo.innerHTML = `
                <span class="file-preview-name">${file.name}</span>
                <span class="file-preview-size">${(file.size / 1024 / 1024).toFixed(1)} MB</span>
            `;
            
            previewDiv.appendChild(videoContainer);
            previewDiv.appendChild(fileInfo);
            previewDiv.appendChild(removeBtn);
            container.appendChild(previewDiv);
        });
    }

    // Form validation enhancement with media replacement logic
    const form = document.querySelector('form');
    const submitBtn = document.querySelector('.btn-primary');

    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            // Validate media limits considering replacements
            const currentImageCount = {{ count($announcement->allImagePaths) }};
            const currentVideoCount = {{ count($announcement->allVideoPaths) }};
            
            const removedImagesCount = removedImageIndexes.length + (removedSingleImage ? 1 : 0);
            const removedVideosCount = removedVideoIndexes.length + (removedSingleVideo ? 1 : 0);
            
            const finalImageCount = (currentImageCount - removedImagesCount) + selectedImages.length;
            const finalVideoCount = (currentVideoCount - removedVideosCount) + selectedVideos.length;
            
            if (finalImageCount > maxImages) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Image Limit Exceeded',
                    text: `Total images cannot exceed ${maxImages}. Current: ${currentImageCount}, Removing: ${removedImagesCount}, Adding: ${selectedImages.length}, Final: ${finalImageCount}`,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'OK'
                });
                return false;
            }
            
            if (finalVideoCount > maxVideos) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Video Limit Exceeded',
                    text: `Total videos cannot exceed ${maxVideos}. Current: ${currentVideoCount}, Removing: ${removedVideosCount}, Adding: ${selectedVideos.length}, Final: ${finalVideoCount}`,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#ef4444'
                });
                return false;
            }
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            submitBtn.disabled = true;
        });
    }
    
    // Initialize media limits on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateMediaLimits();
    });

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

// Mobile sidebar toggle function
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('open');
}

function handleLogout() {
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
}

window.addEventListener('resize', function() {
    const sidebar = document.querySelector('.sidebar');
    if (window.innerWidth > 1024) {
        sidebar.classList.remove('open');
    }
});
</script>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
