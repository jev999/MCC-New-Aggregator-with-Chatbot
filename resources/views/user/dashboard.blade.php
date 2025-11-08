<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - MCC News Aggregator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style @nonce>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .bulletin-board {
            background: #e9e2d0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border-radius: 12px;
            position: relative;
        }
        
        .bulletin-board::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 40px;
            background: #8b5a2b;
            border-radius: 12px 12px 0 0;
        }
        
        .section {
            background: #fff;
            border: 1px solid #d4c9a9;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }
        
        .section:hover {
            transform: translateY(-5px);
        }
        
        .pin {
            position: relative;
        }
        
        .pin::before {
            content: '';
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 16px;
            height: 16px;
            background: radial-gradient(circle, #ffd700 30%, #daa520 90%);
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        /* Modal transitions now handled by Alpine.js */
        
        .item-hover {
            transition: all 0.2s ease;
        }
        
        .item-hover:hover {
            background-color: #f8f9fa;
            padding-left: 8px;
            border-left: 3px solid;
        }
        
        .announcement-hover:hover {
            border-left-color: #3b82f6;
        }
        
        .event-hover:hover {
            border-left-color: #10b981;
        }
        
        .news-hover:hover {
            border-left-color: #ef4444;
        }
        
        .profile-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .profile-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .announcement-item {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 12px;
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
        }
        
        .announcement-item:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        /* Enhanced modal styles - now using Tailwind classes */
        
        .modal-category {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .category-announcement {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .category-event {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .category-news {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        }

        .modal-location-container {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 12px 16px;
            margin: 12px 0;
            box-shadow: 0 2px 4px rgba(34, 197, 94, 0.1);
        }

        .modal-location {
            color: #16a34a;
            font-size: 0.875rem;
            font-weight: 500;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-location::before {
            content: "\f3c5";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 1rem;
            color: #16a34a;
            filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
        }
        
        .modal-content {
            margin-top: 1rem;
            line-height: 1.6;
            color: #374151;
        }
        
        .close-button {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
            transition: color 0.2s;
        }
        
        .close-button:hover {
            color: #374151;
        }
        
        /* Image and video styles */
        .item-media {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        
        .video-container {
            position: relative;
            width: 100%;
            height: 160px;
            overflow: hidden;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        
        .video-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .play-button {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.7);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .play-button i {
            color: white;
            font-size: 20px;
        }
        
        .modal-media {
            width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 1rem;
            max-height: 50vh;
            object-fit: contain;
            object-position: center;
            background-color: #f8f9fa;
        }
        
        .modal-video {
            width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 1rem;
            max-height: 50vh;
            background-color: #000;
        }
        
        /* Enhanced Modal Styles with Media Grid Layout */
        .modal-container {
            max-width: 95vw;
            max-height: 95vh;
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            transform: scale(0.7);
            transition: transform 0.4s ease;
        }

        .modal-container.active {
            transform: scale(1);
        }

        @media (min-width: 640px) {
            .modal-container {
                max-width: 90vw;
                max-width: 900px;
            }
        }

        @media (min-width: 1024px) {
            .modal-container {
                max-width: 85vw;
                max-width: 1000px;
            }
        }

        /* Media Container Grid Layout */
        .modal-media-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .modal-media-item {
            border-radius: 8px;
            overflow: hidden;
            height: 200px;
            background-color: #f8f9fa;
        }

        .modal-media-item img, 
        .modal-media-item video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .modal-video-container {
            grid-column: 1 / -1;
            height: 300px;
            background-color: #000;
            border-radius: 8px;
            overflow: hidden;
        }

        .modal-video-container video {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Single media item layouts */
        .modal-single-image {
            width: 100%;
            height: auto;
            max-height: 70vh;
            border-radius: 8px;
            margin-bottom: 1rem;
            object-fit: contain;
            background-color: #f8f9fa;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .modal-single-video {
            width: 100%;
            height: auto;
            max-height: 50vh;
            border-radius: 8px;
            margin-bottom: 1rem;
            background-color: #000;
        }
        
        .modal-content-area {
            max-height: calc(95vh - 120px);
            overflow-y: auto;
        }
        
        @media (max-width: 768px) {
            .modal-content-area {
                max-height: calc(95vh - 100px);
                padding: 1rem;
            }
            
            .modal-media {
                max-height: 40vh;
            }
            
            .modal-video {
                max-height: 40vh;
            }
            
            /* Mobile modal header adjustments */
            .modal-header {
                padding: 1rem;
            }
            
            .modal-header h3 {
                font-size: 1.25rem;
                line-height: 1.4;
            }
            
            /* Better touch targets for mobile */
            .modal-close-btn {
                padding: 0.5rem;
                min-width: 44px;
                min-height: 44px;
            }
        }
        
        /* Extra small screens */
        @media (max-width: 480px) {
            .modal-container {
                max-width: 98vw;
                margin: 0.5rem;
            }
            
            .modal-content-area {
                padding: 0.75rem;
                max-height: calc(95vh - 80px);
            }
            
            .modal-media {
                max-height: 35vh;
            }
            
            .modal-video {
                max-height: 35vh;
            }
        }
        
        /* Row layout for sections */
        .row-layout {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .section-content {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }
        
        /* Mobile Responsive Styles for Modal Media */
        @media (max-width: 768px) {
            .bulletin-board::before {
                height: 30px;
            }
            
            .section-content {
                grid-template-columns: 1fr;
            }
            
            .modal-media-container {
                grid-template-columns: 1fr;
            }
            
            .modal-media-item {
                height: 150px;
            }
            
            .modal-video-container {
                height: 200px;
            }
        }

        /* Extra small screens */
        @media (max-width: 480px) {
            .modal-media-item {
                height: 120px;
            }
            
            .modal-video-container {
                height: 180px;
            }
        }
        
        /* SweetAlert Custom Styling */
        .swal-popup-custom {
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .swal-title-custom {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: #1f2937;
        }
        
        .swal-content-custom {
            font-family: 'Poppins', sans-serif;
            color: #6b7280;
        }
        
        .swal2-popup {
            font-family: 'Poppins', sans-serif;
        }
        
        .swal2-confirm {
            border-radius: 8px;
            font-weight: 600;
            padding: 10px 20px;
        }
        
        .swal2-cancel {
            border-radius: 8px;
            font-weight: 600;
            padding: 10px 20px;
        }
        
        /* Enhanced Profile Action Buttons */
        .profile-action-btn {
            position: relative;
            overflow: hidden;
        }
        
        .profile-action-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.3s ease, height 0.3s ease;
        }
        
        .profile-action-btn:hover::before {
            width: 100%;
            height: 100%;
        }
        
        /* Mobile responsive adjustments for profile buttons */
        @media (max-width: 768px) {
            .profile-action-btn {
                width: 52px !important;
                height: 52px !important;
                min-height: 52px;
                min-width: 52px;
                touch-action: manipulation;
                -webkit-tap-highlight-color: transparent;
            }
            
            .profile-action-btn i {
                font-size: 1.25rem !important;
            }
        }
        
        @media (max-width: 480px) {
            .profile-action-btn {
                width: 48px !important;
                height: 48px !important;
                min-height: 48px;
                min-width: 48px;
            }
            
            .profile-action-btn i {
                font-size: 1.125rem !important;
            }
        }
        
        @media (max-width: 360px) {
            .profile-action-btn {
                width: 44px !important;
                height: 44px !important;
                min-height: 44px;
                min-width: 44px;
            }
            
            .profile-action-btn i {
                font-size: 1rem !important;
            }
        }
        
        /* Tooltip enhancement */
        .profile-action-btn[title]:hover::after {
            content: attr(title);
            position: absolute;
            bottom: -35px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            white-space: nowrap;
            z-index: 1000;
            opacity: 0;
            animation: tooltipFadeIn 0.3s ease forwards;
        }
        
        @keyframes tooltipFadeIn {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-5px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }
        
        /* Enhanced Profile Modal Buttons */
        .profile-modal-btn {
            position: relative;
            overflow: hidden;
            min-height: 48px;
            font-weight: 600;
            letter-spacing: 0.025em;
            border: none;
            cursor: pointer;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }
        
        .profile-modal-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .profile-modal-btn:hover::before {
            left: 100%;
        }
        
        .profile-modal-btn:active {
            transform: scale(0.98);
        }
        
        /* Button-specific styles */
        .logout-btn:hover {
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
        }
        
        .edit-btn:hover {
            box-shadow: 0 8px 25px rgba(147, 51, 234, 0.4);
        }
        
        .close-btn:hover {
            box-shadow: 0 8px 25px rgba(107, 114, 128, 0.4);
        }
        
        /* Mobile responsive adjustments for profile modal buttons */
        @media (max-width: 640px) {
            .profile-modal-btn {
                min-height: 52px;
                font-size: 0.9375rem;
                padding: 0.875rem 1.5rem !important;
            }
            
            .profile-modal-btn i {
                font-size: 1.125rem;
            }
            
            .profile-modal-btn span {
                font-weight: 600;
            }
        }
        
        @media (max-width: 480px) {
            .profile-modal-btn {
                min-height: 48px;
                font-size: 0.875rem;
                padding: 0.75rem 1.25rem !important;
            }
            
            .profile-modal-btn i {
                font-size: 1rem;
            }
        }
        
        /* Enhanced modal footer */
        .modal-footer-gradient {
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            border-top: 1px solid #e5e7eb;
        }
        
        /* Pulse animation for active states */
        @keyframes buttonPulse {
            0% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
            }
        }
        
        .profile-modal-btn:focus {
            outline: none;
            animation: buttonPulse 1.5s infinite;
        }
        
        /* Profile Edit Icon Styles */
        .profile-edit-icon {
            position: relative;
            overflow: hidden;
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }
        
        .profile-edit-icon::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(147, 51, 234, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.3s ease, height 0.3s ease;
        }
        
        .profile-edit-icon:hover::before {
            width: 100%;
            height: 100%;
        }
        
        .profile-edit-icon:active {
            transform: scale(0.95);
        }
        
        /* Mobile responsive adjustments for profile edit icon */
        @media (max-width: 768px) {
            .profile-edit-icon {
                width: 36px !important;
                height: 36px !important;
                min-height: 36px;
                min-width: 36px;
            }
            
            .profile-edit-icon i {
                font-size: 0.875rem !important;
            }
        }
        
        @media (max-width: 480px) {
            .profile-edit-icon {
                width: 32px !important;
                height: 32px !important;
                min-height: 32px;
                min-width: 32px;
            }
            
            .profile-edit-icon i {
                font-size: 0.75rem !important;
            }
        }
        
        /* Professional Traditional Modal Styles */
        .glass-modal-backdrop {
            background: rgba(0, 0, 0, 0.6);
        }
        
        .glass-modal-container {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .glass-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            padding: 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
        }
        
        .glass-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        }
        
        .glass-close-btn {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            cursor: pointer;
            color: white;
        }
        
        .glass-close-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }
        
        .glass-close-btn:active {
            transform: scale(0.95);
        }
        
        .glass-content {
            background: #f9fafb;
            padding: 2rem;
            overflow-y: auto;
            max-height: calc(95vh - 240px);
        }
        
        .glass-profile-picture {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border: 4px solid #ffffff;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3), 0 0 0 1px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .glass-profile-picture:hover {
            transform: scale(1.03);
            box-shadow: 0 15px 40px rgba(99, 102, 241, 0.4), 0 0 0 1px rgba(0, 0, 0, 0.1);
        }
        
        .glass-footer {
            background: #ffffff;
            padding: 1.5rem 2rem;
            border-top: 1px solid #e5e7eb;
        }
        
        /* Professional Form Elements */
        .glass-content input,
        .glass-content select,
        .glass-content textarea {
            background: #ffffff;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            color: #1f2937;
            transition: all 0.2s ease;
            font-size: 0.9375rem;
        }
        
        .glass-content input:focus,
        .glass-content select:focus,
        .glass-content textarea:focus {
            background: #ffffff;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            outline: none;
        }
        
        .glass-content label {
            color: #374151;
            font-weight: 600;
            font-size: 0.875rem;
            letter-spacing: 0.025em;
        }
        
        /* Professional Info Cards */
        .info-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1rem;
            transition: all 0.2s ease;
        }
        
        .info-card:hover {
            border-color: #c7d2fe;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
            transform: translateY(-2px);
        }
        
        .info-card-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
        }
        
        /* Profile Picture Upload Overlay */
        .profile-picture-overlay {
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
        }
        
        /* Professional Action Buttons */
        .action-button-group {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            margin-top: 1rem;
        }
        
        .action-button {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            font-size: 1.125rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        
        .action-button:active {
            transform: translateY(0);
        }
        
        /* Status Indicator */
        .status-indicator {
            position: absolute;
            bottom: 4px;
            right: 4px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #10b981;
            border: 3px solid #ffffff;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
        }
        
        /* Mobile Responsive Glassmorphism */
        @media (max-width: 768px) {
            .glass-modal-container {
                margin: 0.5rem;
                border-radius: 20px;
                max-width: calc(100vw - 1rem);
                max-height: 95vh;
                width: calc(100vw - 1rem);
            }
            
            .glass-header {
                padding: 1.25rem;
            }
            
            .glass-header h3 {
                font-size: 1.5rem;
            }
            
            .glass-header p {
                font-size: 0.875rem;
            }
            
            .glass-content {
                padding: 1.25rem;
                max-height: calc(95vh - 140px);
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .glass-profile-picture {
                width: 100px;
                height: 100px;
            }
            
            .glass-close-btn {
                width: 44px;
                height: 44px;
                min-width: 44px;
                min-height: 44px;
                padding: 0.5rem;
            }
            
            /* Mobile form inputs */
            .glass-content input[type="text"],
            .glass-content input[type="email"],
            .glass-content select {
                font-size: 16px; /* Prevents zoom on iOS */
                padding: 0.875rem;
                min-height: 48px;
                border-radius: 12px;
            }
            
            /* Mobile buttons */
            .glass-content button {
                min-height: 48px;
                padding: 0.875rem 1.5rem;
                font-size: 1rem;
                border-radius: 12px;
                touch-action: manipulation;
            }
        }
        
        @media (max-width: 480px) {
            .glass-modal-container {
                margin: 0.25rem;
                border-radius: 16px;
                max-width: calc(100vw - 0.5rem);
                width: calc(100vw - 0.5rem);
            }
            
            .glass-header {
                padding: 1rem;
            }
            
            .glass-header h3 {
                font-size: 1.25rem;
            }
            
            .glass-header p {
                font-size: 0.8rem;
            }
            
            .glass-content {
                padding: 1rem;
                max-height: calc(95vh - 120px);
            }
            
            .glass-profile-picture {
                width: 80px;
                height: 80px;
            }
            
            .glass-close-btn {
                width: 40px;
                height: 40px;
                min-width: 40px;
                min-height: 40px;
            }
            
            /* Smaller mobile form inputs */
            .glass-content input[type="text"],
            .glass-content input[type="email"],
            .glass-content select {
                font-size: 16px;
                padding: 0.75rem;
                min-height: 44px;
                border-radius: 10px;
            }
            
            /* Smaller mobile buttons */
            .glass-content button {
                min-height: 44px;
                padding: 0.75rem 1.25rem;
                font-size: 0.9rem;
                border-radius: 10px;
            }
        }
        
        /* Extra small screens */
        @media (max-width: 360px) {
            .glass-modal-container {
                margin: 0.125rem;
                border-radius: 12px;
                max-width: calc(100vw - 0.25rem);
                width: calc(100vw - 0.25rem);
            }
            
            .glass-header {
                padding: 0.875rem;
            }
            
            .glass-header h3 {
                font-size: 1.125rem;
            }
            
            .glass-content {
                padding: 0.875rem;
                max-height: calc(95vh - 100px);
            }
            
            .glass-profile-picture {
                width: 70px;
                height: 70px;
            }
            
            .glass-close-btn {
                width: 36px;
                height: 36px;
                min-width: 36px;
                min-height: 36px;
            }
            
            /* Extra small mobile form inputs */
            .glass-content input[type="text"],
            .glass-content input[type="email"],
            .glass-content select {
                font-size: 16px;
                padding: 0.625rem;
                min-height: 40px;
                border-radius: 8px;
            }
            
            /* Extra small mobile buttons */
            .glass-content button {
                min-height: 40px;
                padding: 0.625rem 1rem;
                font-size: 0.875rem;
                border-radius: 8px;
            }
        }
        
        /* Mobile landscape orientation */
        @media (max-width: 768px) and (orientation: landscape) {
            .glass-modal-container {
                max-height: 90vh;
                margin: 0.25rem;
            }
            
            .glass-content {
                max-height: calc(90vh - 100px);
                padding: 0.75rem;
            }
            
            .glass-header {
                padding: 0.75rem;
            }
            
            .glass-profile-picture {
                width: 60px;
                height: 60px;
            }
        }
        
        /* Prevent zoom on input focus for iOS */
        @media screen and (-webkit-min-device-pixel-ratio: 0) {
            .glass-content input[type="text"],
            .glass-content input[type="email"],
            .glass-content select {
                font-size: 16px !important;
            }
        }
        
        /* Mobile SweetAlert Styles */
        .mobile-swal-popup {
            border-radius: 16px !important;
            margin: 0.5rem !important;
            max-width: calc(100vw - 1rem) !important;
            font-size: 16px !important;
        }
        
        .mobile-swal-title {
            font-size: 1.25rem !important;
            line-height: 1.3 !important;
            margin-bottom: 0.75rem !important;
        }
        
        .mobile-swal-content {
            font-size: 0.95rem !important;
            line-height: 1.4 !important;
            margin-bottom: 1rem !important;
        }
        
        .mobile-swal-button {
            min-height: 44px !important;
            padding: 0.75rem 1.5rem !important;
            font-size: 1rem !important;
            border-radius: 12px !important;
            margin: 0.25rem !important;
            touch-action: manipulation !important;
        }
        
        /* Extra small mobile SweetAlert */
        @media (max-width: 480px) {
            .mobile-swal-popup {
                border-radius: 12px !important;
                margin: 0.25rem !important;
                max-width: calc(100vw - 0.5rem) !important;
            }
            
            .mobile-swal-title {
                font-size: 1.125rem !important;
            }
            
            .mobile-swal-content {
                font-size: 0.9rem !important;
            }
            
            .mobile-swal-button {
                min-height: 40px !important;
                padding: 0.625rem 1.25rem !important;
                font-size: 0.9rem !important;
                border-radius: 10px !important;
            }
        }
        
        /* Glass Animation Effects */
        @keyframes glassShimmer {
            0% {
                background-position: -200% 0;
            }
            100% {
                background-position: 200% 0;
            }
        }
        
        .glass-modal-container:hover::before {
            animation: glassShimmer 2s ease-in-out infinite;
            background: linear-gradient(90deg, 
                transparent, 
                rgba(255, 255, 255, 0.3), 
                transparent
            );
            background-size: 200% 100%;
        }

        /* ===== MOBILE RESPONSIVE STYLES ===== */
        
        /* Mobile Header and Navigation */
        @media (max-width: 768px) {
            body {
                padding: 1rem 0.5rem;
            }
            
            .container {
                padding: 0 0.5rem;
            }
            
            /* Header adjustments */
            header {
                margin-bottom: 1.5rem;
            }
            
            header .flex {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            header h1 {
                font-size: 1.875rem !important;
                line-height: 1.2;
                margin-bottom: 0.5rem;
            }
            
            header p {
                font-size: 0.875rem;
                margin-bottom: 1rem;
            }
            
            /* Notification bell positioning */
            .relative {
                position: fixed !important;
                top: 1rem;
                right: 1rem;
                z-index: 1000;
            }
            
            /* Notification dropdown mobile */
            .fixed.top-20.right-4 {
                top: 4rem !important;
                right: 0.5rem !important;
                left: 0.5rem !important;
                width: auto !important;
                max-width: none !important;
            }
        }
        
        /* Mobile Profile Card */
        @media (max-width: 768px) {
            .profile-card {
                padding: 1rem !important;
                margin-bottom: 1.5rem;
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
            
            .profile-card .flex {
                flex-direction: column;
                align-items: center;
                gap: 0.75rem;
            }
            
            .profile-card .relative {
                margin-right: 0 !important;
                margin-bottom: 0.5rem;
            }
            
            .profile-card h3 {
                font-size: 1.125rem !important;
                margin-bottom: 0.25rem;
            }
            
            .profile-card p {
                font-size: 0.875rem;
            }
            
            .profile-edit-icon {
                margin-top: 0.5rem;
            }
        }
        
        /* Mobile Bulletin Board */
        @media (max-width: 768px) {
            .bulletin-board {
                padding: 1rem !important;
                margin: 0;
            }
            
            .section {
                padding: 1rem !important;
                margin-bottom: 1rem;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
                margin-bottom: 1rem;
            }
            
            .section-header h2 {
                font-size: 1.125rem !important;
            }
            
            .section-header .ml-auto {
                margin-left: 0 !important;
                margin-top: 0.25rem;
            }
        }
        
        /* Mobile Content Grid */
        @media (max-width: 768px) {
            .section-content {
                grid-template-columns: 1fr !important;
                gap: 1rem;
            }
            
            .announcement-item {
                padding: 1rem !important;
            }
            
            .announcement-item .item-media {
                height: 200px !important;
                margin-bottom: 0.75rem;
            }
            
            .announcement-item span {
                font-size: 1rem !important;
                line-height: 1.4;
            }
            
            .announcement-item p {
                font-size: 0.875rem;
                margin-top: 0.5rem;
            }
        }
        
        /* Mobile Modal Adjustments */
        @media (max-width: 768px) {
            .modal-container {
                margin: 0.5rem !important;
                max-width: calc(100vw - 1rem) !important;
                max-height: 95vh !important;
            }
            
            .modal-header {
                padding: 1rem !important;
            }
            
            .modal-header h3 {
                font-size: 1.125rem !important;
                line-height: 1.3;
            }
            
            .modal-content-area {
                padding: 1rem !important;
                max-height: calc(95vh - 120px) !important;
            }
            
            .modal-media-container {
                grid-template-columns: 1fr !important;
                gap: 0.75rem;
            }
            
            .modal-media-item {
                height: 150px !important;
            }
            
            .modal-video-container {
                height: 200px !important;
            }
            
            .modal-single-image {
                max-height: 50vh !important;
            }
            
            .modal-single-video {
                max-height: 40vh !important;
            }
        }
        
        /* Extra Small Screens */
        @media (max-width: 480px) {
            body {
                padding: 0.5rem 0.25rem;
            }
            
            .container {
                padding: 0 0.25rem;
            }
            
            header h1 {
                font-size: 1.5rem !important;
            }
            
            header p {
                font-size: 0.8rem;
            }
            
            .profile-card {
                padding: 0.75rem !important;
            }
            
            .profile-card h3 {
                font-size: 1rem !important;
            }
            
            .bulletin-board {
                padding: 0.75rem !important;
            }
            
            .section {
                padding: 0.75rem !important;
            }
            
            .section-header h2 {
                font-size: 1rem !important;
            }
            
            .announcement-item {
                padding: 0.75rem !important;
            }
            
            .announcement-item .item-media {
                height: 150px !important;
            }
            
            .modal-container {
                margin: 0.25rem !important;
                max-width: calc(100vw - 0.5rem) !important;
            }
            
            .modal-header {
                padding: 0.75rem !important;
            }
            
            .modal-content-area {
                padding: 0.75rem !important;
            }
            
            .modal-media-item {
                height: 120px !important;
            }
            
            .modal-video-container {
                height: 150px !important;
            }
        }
        
        /* Touch-friendly interactions */
        @media (max-width: 768px) {
            .announcement-item,
            .profile-card,
            button {
                min-height: 44px;
                min-width: 44px;
            }
            
            .announcement-item {
                touch-action: manipulation;
            }
            
            .modal-close-btn {
                padding: 0.75rem !important;
                min-width: 48px !important;
                min-height: 48px !important;
            }
            
            .profile-edit-icon {
                min-width: 44px !important;
                min-height: 44px !important;
            }
        }
        
        /* Landscape mobile adjustments */
        @media (max-width: 768px) and (orientation: landscape) {
            .modal-container {
                max-height: 90vh !important;
            }
            
            .modal-content-area {
                max-height: calc(90vh - 100px) !important;
            }
            
            .modal-media-item {
                height: 120px !important;
            }
            
            .modal-video-container {
                height: 150px !important;
            }
        }
        
        /* Toast Notification Styles */
        .toast-notification {
            position: fixed;
            top: -200px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            width: 90%;
            max-width: 500px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            transition: top 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            cursor: pointer;
            user-select: none;
            touch-action: pan-y;
        }
        
        .toast-notification.show {
            top: 20px;
        }
        
        .toast-notification.swiping {
            transition: transform 0.1s linear;
        }
        
        .toast-notification.dismissed {
            animation: slideOutUp 0.4s ease-out forwards;
        }
        
        @keyframes slideOutUp {
            to {
                transform: translateX(-50%) translateY(-200px);
                opacity: 0;
            }
        }
        
        .toast-content {
            padding: 1rem 1.5rem;
            color: white;
            position: relative;
        }
        
        .toast-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .toast-icon {
            font-size: 1.5rem;
            margin-right: 0.75rem;
            animation: bellRing 1s ease-in-out;
        }
        
        @keyframes bellRing {
            0%, 100% { transform: rotate(0deg); }
            10%, 30%, 50%, 70%, 90% { transform: rotate(-10deg); }
            20%, 40%, 60%, 80% { transform: rotate(10deg); }
        }
        
        .toast-title {
            font-weight: 600;
            font-size: 1rem;
            flex: 1;
        }
        
        .toast-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .toast-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .toast-message {
            font-size: 0.875rem;
            opacity: 0.95;
            line-height: 1.4;
            margin-bottom: 0.5rem;
        }
        
        .toast-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.75rem;
            opacity: 0.8;
            margin-top: 0.5rem;
        }
        
        .toast-time {
            display: flex;
            align-items: center;
        }
        
        .toast-swipe-hint {
            display: flex;
            align-items: center;
            opacity: 0.6;
        }
        
        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 0 0 0 12px;
            animation: progress-shrink 5s linear forwards;
        }
        
        @keyframes progress-shrink {
            from { width: 100%; }
            to { width: 0%; }
        }
        
        /* Mobile responsive toast */
        @media (max-width: 640px) {
            .toast-notification {
                width: 95%;
                max-width: none;
            }
            
            .toast-notification.show {
                top: 10px;
            }
            
            .toast-content {
                padding: 0.875rem 1.25rem;
            }
            
            .toast-title {
                font-size: 0.875rem;
            }
            
            .toast-message {
                font-size: 0.8125rem;
            }
            
            .toast-icon {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body class="py-8 px-4" x-data="dashboardData()">
    <!-- Toast Notification Popup -->
    <div x-show="toastVisible" 
         x-ref="toast"
         class="toast-notification"
         :class="{ 'show': toastVisible, 'dismissed': toastDismissed }"
         @click="handleToastClick()"
         @touchstart="handleTouchStart($event)"
         @touchmove="handleTouchMove($event)"
         @touchend="handleTouchEnd($event)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 transform translateY(0)"
         x-transition:leave-end="opacity-0 transform translateY(-100px)">
        <div class="toast-content" x-show="currentToast">
            <div class="toast-header">
                <div class="flex items-center flex-1">
                    <i class="fas fa-bell toast-icon"></i>
                    <span class="toast-title" x-text="currentToast?.title || 'New Content Posted!'"></span>
                </div>
                <button @click.stop="dismissToast()" class="toast-close">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <div class="toast-message" x-text="currentToast?.message"></div>
            <div class="toast-footer">
                <div class="toast-time">
                    <i class="fas fa-clock mr-1"></i>
                    <span>Just now</span>
                </div>
                <div class="toast-swipe-hint">
                    <i class="fas fa-hand-pointer mr-1"></i>
                    <span>Swipe up to dismiss</span>
                </div>
            </div>
            <div class="toast-progress"></div>
        </div>
    </div>
    
    <div class="container mx-auto max-w-7xl">
        <header class="mb-8 text-center relative">
            <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
                <div class="hidden md:block"></div>
                <div class="flex-1">
                    <h1 class="text-2xl md:text-4xl font-bold text-gray-800 mb-2">MCC News Aggregator</h1>
                    <p class="text-sm md:text-base text-gray-600">Your central hub for announcements, events, and news</p>
                </div>
                <div class="relative">
                    <button @click="toggleNotifications()" 
                            class="relative p-3 bg-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-110">
                        <i class="fas fa-bell text-xl md:text-2xl text-gray-600 hover:text-blue-600 transition-colors"></i>
                        <span x-show="notificationCount > 0" 
                              x-text="notificationCount" 
                              class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-6 w-6 flex items-center justify-center font-bold animate-pulse"></span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Notification Dropdown -->
        <div x-show="showNotifications" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="fixed top-20 right-4 z-50 w-80 md:w-80 bg-white rounded-lg shadow-xl border border-gray-200"
             @click.away="showNotifications = false">
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Notifications</h3>
                    <button @click="showNotifications = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="max-h-64 overflow-y-auto">
                <template x-if="notifications.length === 0">
                    <div class="p-4 text-center text-gray-500">
                        <i class="fas fa-bell-slash text-2xl mb-2"></i>
                        <p>No new notifications</p>
                    </div>
                </template>
                <template x-for="notification in notifications" :key="notification.id">
                    <div class="p-4 border-b border-gray-100 hover:bg-gray-50" 
                         :class="{ 'bg-blue-50': !notification.is_read }">
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center" 
                                 :class="notification.is_read ? 'bg-gray-100' : 'bg-blue-100'">
                                <i class="text-sm" 
                                   :class="notification.is_read ? 'fas fa-bell text-gray-600' : 'fas fa-bell text-blue-600'"></i>
                            </div>
                            <div class="flex-1 cursor-pointer" @click="handleNotificationClick(notification)">
                                <p class="text-sm font-medium" 
                                   :class="notification.is_read ? 'text-gray-700' : 'text-gray-900'"
                                   x-text="notification.title"></p>
                                <p class="text-xs text-gray-500" x-text="notification.message"></p>
                                <p class="text-xs text-gray-400 mt-1" x-text="notification.created_at"></p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div x-show="!notification.is_read" class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                <button @click="removeNotification(notification.id)" 
                                        class="text-xs text-gray-500 hover:text-red-600 transition-colors"
                                        title="Remove notification">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Profile Card at the Top -->
        <div class="profile-card bg-white rounded-xl shadow-md p-4 md:p-6 mb-8 flex flex-col md:flex-row items-center justify-between hover:shadow-lg transition-all duration-300 cursor-pointer" @click="profileModal = true">
            <div class="flex flex-col md:flex-row items-center gap-4 md:gap-6 w-full">
                <div class="relative w-16 h-16 md:mr-6">
                    @if(auth()->user()->hasProfilePicture)
                        <img src="{{ auth()->user()->profilePictureUrl }}" 
                             alt="Profile Picture" 
                             class="w-16 h-16 rounded-full object-cover border-2 border-purple-200 shadow-sm">
                    @else
                        <div class="w-16 h-16 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-sm">
                            {{ auth()->user()->initials }}
                        </div>
                    @endif
                    <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 rounded-full border-2 border-white flex items-center justify-center">
                        <i class="fas fa-camera text-white text-xs"></i>
                    </div>
                </div>
                <div class="flex-1 text-center md:text-left">
                    <div class="flex flex-col md:flex-row items-center gap-3">
                        <h3 class="font-semibold text-lg md:text-xl text-gray-800">{{ auth()->user()->first_name }} {{ auth()->user()->surname }}</h3>
                        <button @click.stop="profileModal = true; editMode = true; initializeProfileForm()" 
                                title="Edit Profile"
                                class="profile-edit-icon w-8 h-8 rounded-full bg-purple-100 hover:bg-purple-200 text-purple-600 hover:text-purple-700 transition-all duration-200 flex items-center justify-center hover:scale-110 group border border-purple-200 hover:border-purple-300 shadow-sm hover:shadow-md">
                            <i class="fas fa-edit text-sm group-hover:rotate-12 transition-transform duration-200"></i>
                        </button>
                    </div>
                    <p class="text-sm md:text-base text-gray-600">{{ auth()->user()->department }} - {{ auth()->user()->year_level }}</p>
                    <p class="text-xs md:text-sm text-purple-600 mt-1">Click to view profile</p>
                </div>
            </div>
            <div class="hidden md:block text-purple-500 transform transition-transform group-hover:translate-x-1">
                <i class="fas fa-chevron-right"></i>
            </div>
        </div>

        <main class="bulletin-board p-6 md:p-8">
            <div class="row-layout mt-6">
                <!-- FIRST ROW: Announcements Section -->
                <div class="section p-4 md:p-5 pin">
                    <div class="section-header">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-bullhorn text-blue-500 text-lg md:text-xl"></i>
                            <h2 class="text-lg md:text-xl font-semibold">Announcements</h2>
                        </div>
                        <span class="text-xs md:text-sm text-gray-500">{{ $totalAnnouncements }} total</span>
                    </div>
                    <div class="section-content">
                        @forelse($announcements as $announcement)
                            <div class="announcement-item item-hover announcement-hover p-4 rounded cursor-pointer" 
                                 @click="activeModal = {
                                    title: '{{ addslashes($announcement->title) }}', 
                                    body: '{{ addslashes($announcement->content) }}',
                                    category: 'announcement',
                                    contentId: {{ $announcement->id }},
                                    date: 'Posted: {{ $announcement->created_at->format('M d, Y') }}',
                                    media: '{{ $announcement->hasMedia }}',
                                    mediaUrl: '{{ $announcement->mediaUrl ?? '' }}',
                                    allImageUrls: {{ json_encode($announcement->allImageUrls ?? []) }},
                                    allVideoUrls: {{ json_encode($announcement->allVideoUrls ?? []) }},
                                    videoUrl: '{{ $announcement->hasMedia === 'both' && $announcement->allVideoUrls ? $announcement->allVideoUrls[0] : ($announcement->hasMedia === 'video' ? $announcement->mediaUrl : '') }}',
                                    publisher: '{{ $announcement->admin->role === 'superadmin' ? 'MCC Administration' : ($announcement->admin->role === 'department_admin' ? $announcement->admin->department_display . ' Department' : ($announcement->admin->role === 'office_admin' ? $announcement->admin->office_display : $announcement->admin->username)) }}'
                                 }">
                                @if($announcement->hasMedia === 'image' || $announcement->hasMedia === 'both')
                                    <img src="{{ $announcement->mediaUrl }}" 
                                         alt="{{ $announcement->title }}" class="item-media">
                                @elseif($announcement->hasMedia === 'video')
                                    <div class="video-container">
                                        <video class="item-media" muted>
                                            <source src="{{ $announcement->mediaUrl }}" type="video/mp4">
                                        </video>
                                        <div class="play-button">
                                            <i class="fas fa-play"></i>
                                        </div>
                                    </div>
                                @else
                                    <div class="item-media bg-gray-100 flex items-center justify-center">
                                        <i class="fas fa-bullhorn text-gray-400 text-4xl"></i>
                                    </div>
                                @endif
                                <span class="font-medium block">{{ $announcement->title }}</span>
                                <p class="text-sm text-gray-500 mt-1">Posted: {{ $announcement->created_at->format('M d, Y') }}</p>
                                @if($announcement->hasMedia === 'both')
                                    <div class="flex items-center mt-1 text-xs text-blue-600">
                                        <i class="fas fa-images mr-1"></i>
                                        <i class="fas fa-video mr-1"></i>
                                        <span>Images & Videos</span>
                                    </div>
                                @endif
                                <div class="flex items-center mt-2 text-xs text-gray-600">
                                    <i class="fas fa-user-shield mr-1"></i>
                                    <span>
                                        @if($announcement->admin->role === 'superadmin')
                                            MCC Administration
                                        @elseif($announcement->admin->role === 'department_admin')
                                            {{ $announcement->admin->department_display }} Department
                                        @elseif($announcement->admin->role === 'office_admin')
                                            {{ $announcement->admin->office_display }}
                                        @else
                                            {{ $announcement->admin->username }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-8">
                                <i class="fas fa-bullhorn text-gray-400 text-4xl mb-4"></i>
                                <p class="text-gray-500">No announcements available</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- SECOND ROW: Events Section -->
                <div class="section p-4 md:p-5 pin">
                    <div class="section-header">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-green-500 text-lg md:text-xl"></i>
                            <h2 class="text-lg md:text-xl font-semibold">Events</h2>
                        </div>
                        <span class="text-xs md:text-sm text-gray-500">{{ $totalEvents }} total</span>
                    </div>
                    <div class="section-content">
                        @forelse($events as $event)
                            <div class="announcement-item item-hover event-hover p-4 rounded cursor-pointer" 
                                 @click="activeModal = {
                                    title: '{{ addslashes($event->title) }}', 
                                    body: '{{ addslashes($event->description) }}',
                                    category: 'event',
                                    contentId: {{ $event->id }},
                                    date: 'Date: {{ $event->event_date ? $event->event_date->format('M d, Y') : 'TBD' }}',
                                    location: 'Location: {{ $event->location ?? 'No location specified' }}',
                                    media: '{{ $event->hasMedia }}',
                                    mediaUrl: '{{ $event->mediaUrl ?? '' }}',
                                    allImageUrls: {{ json_encode($event->allImageUrls ?? []) }},
                                    allVideoUrls: {{ json_encode($event->allVideoUrls ?? []) }},
                                    videoUrl: '{{ $event->hasMedia === 'both' && $event->allVideoUrls ? $event->allVideoUrls[0] : ($event->hasMedia === 'video' ? $event->mediaUrl : '') }}',
                                    publisher: '{{ $event->admin->role === 'superadmin' ? 'MCC Administration' : ($event->admin->role === 'department_admin' ? $event->admin->department_display . ' Department' : ($event->admin->role === 'office_admin' ? $event->admin->office_display : $event->admin->username)) }}'
                                 }">
                                @if($event->hasMedia === 'image' || $event->hasMedia === 'both')
                                    <img src="{{ $event->mediaUrl }}" 
                                         alt="{{ $event->title }}" class="item-media">
                                @elseif($event->hasMedia === 'video')
                                    <div class="video-container">
                                        <video class="item-media" muted>
                                            <source src="{{ $event->mediaUrl }}" type="video/mp4">
                                        </video>
                                        <div class="play-button">
                                            <i class="fas fa-play"></i>
                                        </div>
                                    </div>
                                @else
                                    <div class="item-media bg-gray-100 flex items-center justify-center">
                                        <i class="fas fa-calendar-alt text-gray-400 text-4xl"></i>
                                    </div>
                                @endif
                                <span class="font-medium block">{{ $event->title }}</span>
                                <p class="text-sm text-gray-500 mt-1">Date: {{ $event->event_date ? $event->event_date->format('M d, Y') : 'TBD' }}</p>
                                @if($event->location)
                                    <p class="text-sm text-green-600 mt-1 flex items-center">
                                        <i class="fas fa-map-marker-alt mr-1 text-xs"></i>
                                        {{ $event->location }}
                                    </p>
                                @endif
                                @if($event->hasMedia === 'both')
                                    <div class="flex items-center mt-1 text-xs text-green-600">
                                        <i class="fas fa-images mr-1"></i>
                                        <i class="fas fa-video mr-1"></i>
                                        <span>Images & Videos</span>
                                    </div>
                                @endif
                                <div class="flex items-center mt-2 text-xs text-gray-600">
                                    <i class="fas fa-user-shield mr-1"></i>
                                    <span>
                                        @if($event->admin->role === 'superadmin')
                                            MCC Administration
                                        @elseif($event->admin->role === 'department_admin')
                                            {{ $event->admin->department_display }} Department
                                        @elseif($event->admin->role === 'office_admin')
                                            {{ $event->admin->office_display }}
                                        @else
                                            {{ $event->admin->username }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-8">
                                <i class="fas fa-calendar-alt text-gray-400 text-4xl mb-4"></i>
                                <p class="text-gray-500">No events available</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- THIRD ROW: News Section -->
                <div class="section p-4 md:p-5 pin">
                    <div class="section-header">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-newspaper text-red-500 text-lg md:text-xl"></i>
                            <h2 class="text-lg md:text-xl font-semibold">News</h2>
                        </div>
                        <span class="text-xs md:text-sm text-gray-500">{{ $totalNews }} total</span>
                    </div>
                    <div class="section-content">
                        @forelse($news as $article)
                            <div class="announcement-item item-hover news-hover p-4 rounded cursor-pointer" 
                                 @click="activeModal = {
                                    title: '{{ addslashes($article->title) }}', 
                                    body: '{{ addslashes($article->content) }}',
                                    category: 'news',
                                    contentId: {{ $article->id }},
                                    date: 'Published: {{ $article->created_at->format('M d, Y') }}',
                                    media: '{{ $article->hasMedia }}',
                                    mediaUrl: '{{ $article->mediaUrl ?? '' }}',
                                    allImageUrls: {{ json_encode($article->allImageUrls ?? []) }},
                                    allVideoUrls: {{ json_encode($article->allVideoUrls ?? []) }},
                                    videoUrl: '{{ $article->hasMedia === 'both' && $article->allVideoUrls ? $article->allVideoUrls[0] : ($article->hasMedia === 'video' ? $article->mediaUrl : '') }}',
                                    publisher: '{{ $article->admin->role === 'superadmin' ? 'MCC Administration' : ($article->admin->role === 'department_admin' ? $article->admin->department_display . ' Department' : ($article->admin->role === 'office_admin' ? $article->admin->office_display : $article->admin->username)) }}'
                                 }">
                                @if($article->hasMedia === 'image' || $article->hasMedia === 'both')
                                    <img src="{{ $article->mediaUrl }}" 
                                         alt="{{ $article->title }}" class="item-media">
                                @elseif($article->hasMedia === 'video')
                                    <div class="video-container">
                                        <video class="item-media" muted>
                                            <source src="{{ $article->mediaUrl }}" type="video/mp4">
                                        </video>
                                        <div class="play-button">
                                            <i class="fas fa-play"></i>
                                        </div>
                                    </div>
                                @else
                                    <div class="item-media bg-gray-100 flex items-center justify-center">
                                        <i class="fas fa-newspaper text-gray-400 text-4xl"></i>
                                    </div>
                                @endif
                                <span class="font-medium block">{{ $article->title }}</span>
                                <p class="text-sm text-gray-500 mt-1">Published: {{ $article->created_at->format('M d, Y') }}</p>
                                @if($article->hasMedia === 'both')
                                    <div class="flex items-center mt-1 text-xs text-red-600">
                                        <i class="fas fa-images mr-1"></i>
                                        <i class="fas fa-video mr-1"></i>
                                        <span>Images & Videos</span>
                                    </div>
                                @endif
                                <div class="flex items-center mt-2 text-xs text-gray-600">
                                    <i class="fas fa-user-shield mr-1"></i>
                                    <span>
                                        @if($article->admin->role === 'superadmin')
                                            MCC Administration
                                        @elseif($article->admin->role === 'department_admin')
                                            {{ $article->admin->department_display }} Department
                                        @elseif($article->admin->role === 'office_admin')
                                            {{ $article->admin->office_display }}
                                        @else
                                            {{ $article->admin->username }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-8">
                                <i class="fas fa-newspaper text-gray-400 text-4xl mb-4"></i>
                                <p class="text-gray-500">No news available</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </main>

        <!-- Content Modal -->
        <div x-show="activeModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black bg-opacity-50 flex items-start justify-center overflow-y-auto z-50 p-4"
             @click.self="activeModal = null; playingVideo = null; comments = []; replyingTo = null; replyContent = ''; commentContent = ''" 
             @keydown.escape="activeModal = null; playingVideo = null; comments = []; replyingTo = null; replyContent = ''; commentContent = ''">
            <div class="modal-container overflow-hidden flex flex-col mt-6 active"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 @click.stop>
                <div class="p-6 border-b border-gray-200 flex items-center justify-between modal-header">
                    <h3 class="text-2xl font-bold text-gray-800" x-text="activeModal?.title"></h3>
                    <button class="text-gray-400 hover:text-gray-600 transition-colors modal-close-btn" @click="activeModal = null; playingVideo = null; comments = []; replyingTo = null; replyContent = ''; commentContent = ''">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="p-6 modal-content-area pb-28">
                    <template x-if="activeModal">
                        <div>
                            <span class="modal-category" :class="{
                                'category-announcement': activeModal.category === 'announcement',
                                'category-event': activeModal.category === 'event',
                                'category-news': activeModal.category === 'news'
                            }" x-text="activeModal.category.charAt(0).toUpperCase() + activeModal.category.slice(1)"></span>
                            
                            <!-- Single or Multiple Images Display -->
                            <template x-if="activeModal.media === 'image'">
                                <div>
                                    <template x-if="activeModal.allImageUrls && activeModal.allImageUrls.length > 1">
                                        <!-- Multiple Images Grid -->
                                        <div class="modal-media-container">
                                            <template x-for="(imageUrl, index) in activeModal.allImageUrls.slice(0, 2)" :key="index">
                                                <div class="modal-media-item">
                                                    <img :src="imageUrl" 
                                                         :alt="activeModal.title + ' - Image ' + (index + 1)"
                                                         class="cursor-pointer hover:opacity-80 transition-opacity"
                                                         @click="selectedImage = imageUrl; imageModal = true">
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="!activeModal.allImageUrls || activeModal.allImageUrls.length <= 1">
                                        <!-- Single Image -->
                                        <div class="w-full flex justify-center">
                                            <img :src="activeModal.mediaUrl" 
                                                 :alt="activeModal.title" 
                                                 class="modal-single-image cursor-pointer hover:opacity-80 transition-opacity"
                                                 @click="selectedImage = activeModal.mediaUrl; imageModal = true">
                                        </div>
                                    </template>
                                </div>
                            </template>
                            
                            <!-- Single Video Display -->
                            <template x-if="activeModal.media === 'video'">
                                <div class="relative">
                                    <video :src="activeModal.mediaUrl" 
                                           controls 
                                           class="modal-single-video"
                                           x-ref="modalVideo"
                                           preload="metadata"
                                           playsinline>
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            </template>
                            
                            <!-- Both Images and Videos - Enhanced Grid Layout -->
                            <template x-if="activeModal.media === 'both'">
                                <div>
                                    <!-- Images Section - Render single image centered when exactly one, otherwise grid up to 2 -->
                                    <template x-if="activeModal.allImageUrls && activeModal.allImageUrls.length === 1">
                                        <div class="w-full flex justify-center mb-4">
                                            <img :src="activeModal.allImageUrls[0]" 
                                                 :alt="activeModal.title + ' - Image 1'"
                                                 class="modal-single-image cursor-pointer hover:opacity-80 transition-opacity"
                                                 @click="selectedImage = activeModal.allImageUrls[0]; imageModal = true">
                                        </div>
                                    </template>
                                    <template x-if="activeModal.allImageUrls && activeModal.allImageUrls.length > 1">
                                        <div class="modal-media-container">
                                            <template x-for="(imageUrl, index) in activeModal.allImageUrls.slice(0, 2)" :key="index">
                                                <div class="modal-media-item">
                                                    <img :src="imageUrl" 
                                                         :alt="activeModal.title + ' - Image ' + (index + 1)"
                                                         class="cursor-pointer hover:opacity-80 transition-opacity"
                                                         @click="selectedImage = imageUrl; imageModal = true">
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    
                                    <!-- Videos Section -->
                                    <div x-show="activeModal.allVideoUrls && activeModal.allVideoUrls.length > 0">
                                        <template x-for="(videoUrl, index) in activeModal.allVideoUrls.slice(0, 1)" :key="index">
                                            <div class="modal-video-container">
                                                <video :src="videoUrl" 
                                                       controls 
                                                       x-ref="modalVideo"
                                                       preload="metadata"
                                                       playsinline>
                                                    Your browser does not support the video tag.
                                                </video>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                            
                            <p class="modal-date" x-text="activeModal.date"></p>
                            <template x-if="activeModal.category === 'event' && activeModal.location">
                                <div class="modal-location-container">
                                    <p class="modal-location" x-text="activeModal.location"></p>
                                </div>
                            </template>
                            <div class="modal-content" x-text="activeModal.body"></div>
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-user-shield mr-2"></i>
                                    <span x-text="activeModal.publisher"></span>
                                </div>
                            </div>
                            
                            <!-- Comments Section -->
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-lg font-semibold text-gray-800">
                                        <i class="fas fa-comments mr-2 text-blue-500"></i>
                                        Comments
                                        <span x-show="comments.length > 0" class="ml-2 text-sm font-normal text-gray-500" x-text="'(' + comments.length + ')'"></span>
                                    </h4>
                                </div>
                                
                                <!-- Comments Container -->
                                <div class="space-y-4">
                                    <!-- Comment Form -->
                                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                        <form @submit.prevent="submitComment()">
                                            <div class="flex items-start space-x-3">
                                                <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                                    {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->surname, 0, 1) }}
                                                </div>
                                                <div class="flex-1">
                                                    <textarea x-model="commentContent" 
                                                              placeholder="Share your thoughts..."
                                                              class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                                              rows="3"
                                                              maxlength="1000"
                                                              :disabled="submittingComment"
                                                              required></textarea>
                                                    <div class="flex justify-between items-center mt-2">
                                                        <div class="text-xs text-gray-500">
                                                            <span x-text="commentContent.length"></span>/1000 characters
                                                        </div>
                                                        <button type="submit" 
                                                                :disabled="submittingComment || !commentContent.trim()"
                                                                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors flex items-center text-sm">
                                                            <i class="fas fa-paper-plane mr-2" x-show="!submittingComment"></i>
                                                            <i class="fas fa-spinner fa-spin mr-2" x-show="submittingComment"></i>
                                                            <span x-text="submittingComment ? 'Posting...' : 'Post Comment'"></span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <!-- Comments List -->
                                    <div class="space-y-3" x-show="comments.length > 0">
                                        <template x-for="comment in comments" :key="comment.id">
                                            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                                <div class="flex items-start space-x-3">
                                                    <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                                        <span x-text="comment.user.first_name.charAt(0) + comment.user.surname.charAt(0)"></span>
                                                    </div>
                                                    <div class="flex-1">
                                                        <div class="flex items-center justify-between mb-1">
                                                            <div class="flex items-center space-x-2">
                                                                <span class="font-semibold text-blue-600 text-sm" x-text="comment.user.first_name + '_' + comment.user.surname.toLowerCase()"></span>
                                                                <span class="text-xs text-gray-500" x-text="comment.time_ago"></span>
                                                            </div>
                                                            <div class="flex items-center space-x-2">
                                                                <button @click="deleteComment(comment.id)" 
                                                                        class="text-xs text-gray-500 hover:text-red-600 transition-colors"
                                                                        x-show="comment.user_id === {{ auth()->user()->id }}">
                                                                    Remove
                                                                </button>
                                                                <span class="text-gray-300" x-show="comment.user_id === {{ auth()->user()->id }}">•</span>
                                                                <button @click="startReply(comment.id)" 
                                                                        class="text-xs text-gray-500 hover:text-blue-600 transition-colors">
                                                                    Reply
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="text-gray-800 text-sm mb-2" x-text="comment.content"></div>
                                                        
                                                        <!-- Replies -->
                                                        <div x-show="comment.replies && comment.replies.length > 0" class="mt-3 ml-4 space-y-2">
                                                            <template x-for="reply in comment.replies" :key="reply.id">
                                                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                                                    <div class="flex items-start space-x-2">
                                                                        <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center text-white font-semibold text-xs">
                                                                            <span x-text="reply.user.first_name.charAt(0) + reply.user.surname.charAt(0)"></span>
                                                                        </div>
                                                                        <div class="flex-1">
                                                                            <div class="flex items-center justify-between mb-1">
                                                                                <div class="flex items-center space-x-2">
                                                                                    <span class="font-semibold text-green-600 text-xs" x-text="reply.user.first_name + '_' + reply.user.surname.toLowerCase()"></span>
                                                                                    <span class="text-xs text-gray-500" x-text="reply.time_ago"></span>
                                                                                </div>
                                                                                <div class="flex items-center space-x-2">
                                                                                    <button @click="deleteComment(reply.id)" 
                                                                                            class="text-xs text-gray-500 hover:text-red-600 transition-colors"
                                                                                            x-show="reply.user_id === {{ auth()->user()->id }}">
                                                                                        Remove
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                            <div class="text-gray-700 text-xs" x-text="reply.content"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </div>
                                                        
                                                        <!-- Reply Form (shown when replying to this comment) -->
                                                        <div x-show="replyingTo === comment.id" x-transition class="mt-4 p-3 bg-gray-50 rounded-lg border">
                                                            <form @submit.prevent="submitReply(comment.id)">
                                                                <div class="flex items-start space-x-3">
                                                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-xs">
                                                                        {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->surname, 0, 1) }}
                                                                    </div>
                                                                    <div class="flex-1">
                                                                        <textarea x-model="replyContent" 
                                                                                  placeholder="Write a reply..."
                                                                                  class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none text-sm"
                                                                                  rows="2"
                                                                                  maxlength="1000"
                                                                                  :disabled="submittingReply"
                                                                                  required></textarea>
                                                                        <div class="flex justify-between items-center mt-2">
                                                                            <div class="text-xs text-gray-500">
                                                                                <span x-text="replyContent.length"></span>/1000 characters
                                                                            </div>
                                                                            <div class="flex space-x-2">
                                                                                <button type="button" 
                                                                                        @click="cancelReply()"
                                                                                        class="px-3 py-1 text-xs text-gray-600 hover:text-gray-800 transition-colors">
                                                                                    Cancel
                                                                                </button>
                                                                                <button type="submit" 
                                                                                        :disabled="submittingReply || !replyContent.trim()"
                                                                                        class="px-3 py-1 bg-blue-500 text-white rounded text-xs hover:bg-blue-600 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors">
                                                                                    <span x-text="submittingReply ? 'Posting...' : 'Reply'"></span>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    
                                    <!-- No Comments Message -->
                                    <div x-show="comments.length === 0 && !loadingComments" class="text-center py-8 text-gray-500">
                                        <i class="fas fa-comment-slash text-4xl mb-4"></i>
                                        <p>No comments yet. Be the first to comment!</p>
                                    </div>
                                    
                                    <!-- Loading State -->
                                    <div x-show="loadingComments" class="text-center py-8">
                                        <i class="fas fa-spinner fa-spin text-2xl text-blue-500 mb-4"></i>
                                        <p class="text-gray-500">Loading comments...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="p-6 border-t border-gray-200 flex justify-end">
                    <button class="px-6 py-2 rounded-lg bg-gray-800 text-white hover:bg-gray-900 transition-colors" 
                            @click="activeModal = null; playingVideo = null; comments = []; replyingTo = null; replyContent = ''; commentContent = ''">
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Glassmorphism Profile Modal -->
        <div x-show="profileModal" 
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 glass-modal-backdrop flex items-center justify-center z-50 p-2 sm:p-4" 
             @keydown.escape="profileModal = false; editMode = false; resetProfileForm()"
             @click="profileModal = false; editMode = false; resetProfileForm()">
            <div class="glass-modal-container max-w-lg w-full max-h-[95vh] overflow-hidden"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 transform scale-90 translate-y-8"
                 x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 transform scale-90 translate-y-8"
                 @click.stop
                 x-init="$watch('editMode', () => { $nextTick(() => { if ($refs.modalContent) $refs.modalContent.scrollTop = 0; }); })"
                
                <!-- Glassmorphism Header -->
                <div class="glass-header">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold text-white drop-shadow-lg">My Profile</h3>
                            <p class="text-white text-opacity-90 text-sm drop-shadow-sm">Manage your account information</p>
                        </div>
                        <button @click="profileModal = false; editMode = false; resetProfileForm()" 
                                class="glass-close-btn">
                            <i class="fas fa-times text-white"></i>
                        </button>
                    </div>
                </div>

                <!-- Glassmorphism Content -->
                <div class="glass-content" x-ref="modalContent">
                    <!-- Profile Picture Section -->
                    <div class="flex flex-col items-center mb-6">
                        <div class="relative group">
                            <div class="glass-profile-picture">
                                @if(auth()->user()->hasProfilePicture)
                                    <img x-ref="profileImage" 
                                         src="{{ auth()->user()->profilePictureUrl }}" 
                                         alt="Profile Picture" 
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-white font-bold text-4xl">
                                        {{ auth()->user()->initials }}
                                    </div>
                                @endif
                                <!-- Status Indicator -->
                                <div class="status-indicator"></div>
                            </div>
                            
                            <!-- Upload/Change Picture Button (Only in Edit Mode) -->
                            <div x-show="editMode" 
                                 class="absolute inset-0 rounded-full profile-picture-overlay opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center cursor-pointer"
                                 @click="$refs.profilePictureInput.click()">
                                <div class="text-center text-white">
                                    <i class="fas fa-camera text-3xl mb-2"></i>
                                    <p class="text-sm font-semibold">Change Photo</p>
                                </div>
                            </div>
                            
                            <!-- Hidden File Input -->
                            <input type="file" 
                                   x-ref="profilePictureInput" 
                                   accept="image/jpeg,image/png,image/jpg" 
                                   @change="handleProfilePictureChange($event)" 
                                   class="hidden">
                        </div>
                        
                        <!-- Profile Picture Actions (Only in Edit Mode) -->
                        <div class="action-button-group" x-show="editMode">
                            <button @click="$refs.profilePictureInput.click()" 
                                    class="action-button bg-indigo-500 text-white hover:bg-indigo-600"
                                    title="Upload Photo">
                                <i class="fas fa-camera"></i>
                            </button>
                            @if(auth()->user()->hasProfilePicture)
                                <button @click="removeProfilePicture()" 
                                        class="action-button bg-red-500 text-white hover:bg-red-600"
                                        title="Remove Photo">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </div>
                        
                        <h3 class="font-bold text-2xl text-gray-900 mt-4" x-text="editMode ? 'Edit Profile' : '{{ auth()->user()->first_name }} {{ auth()->user()->surname }}'"></h3>
                        <p class="text-gray-600 text-sm mt-1" x-show="!editMode">{{ auth()->user()->department }}</p>
                        <div class="mt-2" x-show="!editMode">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                <i class="fas fa-graduation-cap mr-1"></i>
                                {{ auth()->user()->year_level }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Profile Information -->
                    <div x-show="!editMode" class="space-y-3">
                        <div class="info-card">
                            <div class="flex items-center">
                                <div class="info-card-icon bg-indigo-50 text-indigo-600 mr-3">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-0.5">MS365 Account</p>
                                    <p class="text-gray-900 font-medium">{{ auth()->user()->email }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <div class="flex items-center">
                                <div class="info-card-icon bg-blue-50 text-blue-600 mr-3">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-0.5">Department</p>
                                    <p class="text-gray-900 font-medium">{{ auth()->user()->department }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <div class="flex items-center">
                                <div class="info-card-icon bg-emerald-50 text-emerald-600 mr-3">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-0.5">Year Level</p>
                                    <p class="text-gray-900 font-medium">{{ auth()->user()->year_level }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <div class="flex items-center">
                                <div class="info-card-icon bg-amber-50 text-amber-600 mr-3">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-0.5">Member Since</p>
                                    <p class="text-gray-900 font-medium">{{ auth()->user()->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Edit Profile Form -->
                    <div x-show="editMode" x-transition class="space-y-4">
                        <form @submit.prevent="updateProfile()">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                    <input type="text" 
                                           x-model="profileForm.first_name" 
                                           @input="profileForm.first_name = validateNameInput(profileForm.first_name)"
                                           pattern="^[a-zA-Z]+([a-zA-Z\s]*[a-zA-Z])?$"
                                           title="Only letters and spaces are allowed. No numbers or symbols."
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                           required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                    <input type="text" 
                                           x-model="profileForm.surname" 
                                           @input="profileForm.surname = validateNameWithHyphen(profileForm.surname)"
                                           pattern="^[a-zA-Z]+([a-zA-Z\s]*[a-zA-Z]|[a-zA-Z]*-[a-zA-Z]+)*$"
                                           title="Only letters, spaces, and hyphens in the middle of names are allowed (e.g., Bayonon-on)"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                           required>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Middle Name (Optional)</label>
                                <input type="text" 
                                       x-model="profileForm.middle_name" 
                                       @input="profileForm.middle_name = validateNameWithHyphen(profileForm.middle_name)"
                                       pattern="^$|^[a-zA-Z]+([a-zA-Z\s]*[a-zA-Z]|[a-zA-Z]*-[a-zA-Z]+)*$"
                                       title="Only letters, spaces, and hyphens in the middle of names are allowed (e.g., Bayonon-on)"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                                <select x-model="profileForm.department" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        required>
                                    <option value="Bachelor of Science in Information Technology">Bachelor of Science in Information Technology</option>
                                    <option value="Bachelor of Science in Business Administration">Bachelor of Science in Business Administration</option>
                                    <option value="Bachelor of Elementary Education">Bachelor of Elementary Education</option>
                                    <option value="Bachelor of Science in Hospitality Management">Bachelor of Science in Hospitality Management</option>
                                    <option value="Bachelor of Secondary Education">Bachelor of Secondary Education</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Year Level</label>
                                <select x-model="profileForm.year_level" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        required>
                                    <option value="1st Year">1st Year</option>
                                    <option value="2nd Year">2nd Year</option>
                                    <option value="3rd Year">3rd Year</option>
                                    <option value="4th Year">4th Year</option>
                                </select>
                            </div>
                            
                            <div class="flex space-x-3 pt-4">
                                <button type="submit" 
                                        :disabled="updatingProfile"
                                        class="flex-1 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 disabled:from-gray-400 disabled:to-gray-400 disabled:cursor-not-allowed transition-all duration-200 flex items-center justify-center font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 active:scale-95">
                                    <i class="fas fa-save mr-2" x-show="!updatingProfile"></i>
                                    <i class="fas fa-spinner fa-spin mr-2" x-show="updatingProfile"></i>
                                    <span x-text="updatingProfile ? 'Saving...' : 'Save Changes'"></span>
                                </button>
                                <button type="button" 
                                        @click="editMode = false; resetProfileForm()" 
                                        class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-all duration-200 font-semibold shadow hover:shadow-md transform hover:scale-105 active:scale-95">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Professional Footer -->
                <div class="glass-footer">
                    <div class="flex justify-between items-center gap-3" x-show="!editMode">
                        <!-- Edit Profile Button -->
                        <button @click="editMode = true; initializeProfileForm()" 
                                class="flex-1 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 flex items-center justify-center font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 active:scale-95">
                            <i class="fas fa-edit mr-2"></i>
                            <span>Edit Profile</span>
                        </button>
                        <!-- Logout Button -->
                        <button @click="logout()" 
                                class="px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg hover:from-red-600 hover:to-red-700 transition-all duration-200 flex items-center justify-center font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 active:scale-95">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            <span>Logout</span>
                        </button>
                    </div>
                    <div class="text-center text-sm text-gray-500" x-show="editMode">
                        <p>Update your profile information above</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Image Viewer Modal -->
        <div x-show="imageModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 p-4" 
             @click="imageModal = false; selectedImage = null"
             @keydown.escape="imageModal = false; selectedImage = null">
            <div class="relative max-w-6xl max-h-full overflow-hidden"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 @click.stop>
                <button @click="imageModal = false; selectedImage = null"
                        class="absolute top-4 right-4 w-12 h-12 bg-black bg-opacity-50 text-white rounded-full flex items-center justify-center hover:bg-opacity-70 transition-all z-10">
                    <i class="fas fa-times text-xl"></i>
                </button>
                <img :src="selectedImage" 
                     :alt="'Large view of image'"
                     class="max-w-full max-h-full object-contain rounded-lg shadow-2xl">
            </div>
        </div>
    </div>

    <script>
        // Alpine.js data function
        function dashboardData() {
            return {
                profileModal: false,
                editMode: false,
                profileForm: {
                    first_name: '{{ auth()->user()->first_name }}',
                    middle_name: '{{ auth()->user()->middle_name }}',
                    surname: '{{ auth()->user()->surname }}',
                    department: '',
                    year_level: ''
                },
                updatingProfile: false,
                uploadingPicture: false,
                activeModal: null,
                playingVideo: null,
                imageModal: false,
                selectedImage: null,
                showComments: true,
                commentContent: '',
                comments: [],
                loadingComments: false,
                submittingComment: false,
                replyingTo: null,
                replyContent: '',
                submittingReply: false,
                notificationCount: 0,
                notifications: [],
                showNotifications: false,
                toastVisible: false,
                currentToast: null,
                toastDismissed: false,
                toastQueue: [],
                lastNotificationIds: new Set(),
                touchStartY: 0,
                touchCurrentY: 0,
                isSwiping: false,
                
                // Comments are now always visible, no toggle needed
                
                // Auto-load comments when modal opens and notifications on page load
                init() {
                    this.$watch('activeModal', (newModal) => {
                        if (newModal) {
                            this.loadComments();
                        }
                    });
                    
                    // Load notifications on page load
                    this.loadNotifications();
                    
                    // Refresh notifications every 15 seconds for more responsive updates
                    setInterval(() => {
                        this.loadNotifications();
                    }, 15000);
                    
                    // Also refresh notifications when the page becomes visible (user switches tabs)
                    document.addEventListener('visibilitychange', () => {
                        if (!document.hidden) {
                            this.loadNotifications();
                        }
                    });
                },
                
                loadComments() {
                    if (!this.activeModal) return;
                    
                    // Clear any existing comments and reset state
                    this.comments = [];
                    this.replyingTo = null;
                    this.replyContent = '';
                    this.commentContent = '';
                    
                    this.loadingComments = true;
                    const contentType = this.activeModal.category;
                    const contentId = this.activeModal.contentId;
                    
                    if (!contentId) {
                        this.loadingComments = false;
                        return;
                    }

                    fetch(`/user/content/${contentType}/${contentId}/comments`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.comments = data.comments;
                        } else {
                            console.error('Error loading comments:', data.error);
                            this.comments = [];
                        }
                    })
                    .catch(error => {
                        console.error('Error loading comments:', error);
                        this.comments = [];
                    })
                    .finally(() => {
                        this.loadingComments = false;
                    });
                },
                
                submitComment() {
                    if (!this.activeModal || !this.commentContent.trim()) return;
                    
                    this.submittingComment = true;
                    const contentType = this.activeModal.category;
                    const contentId = this.activeModal.contentId;
                    
                    if (!contentId) {
                        this.submittingComment = false;
                        return;
                    }

                    fetch('/user/comments', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            content: this.commentContent,
                            content_type: contentType,
                            content_id: contentId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.commentContent = '';
                            this.loadComments();
                        } else {
                            alert('Error posting comment: ' + (data.error || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error posting comment:', error);
                        alert('Error posting comment. Please try again.');
                    })
                    .finally(() => {
                        this.submittingComment = false;
                    });
                },
                
                deleteComment(commentId) {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this action!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel',
                        background: '#ffffff',
                        customClass: {
                            popup: 'swal-popup-custom',
                            title: 'swal-title-custom',
                            content: 'swal-content-custom'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show loading
                            Swal.fire({
                                title: 'Deleting...',
                                text: 'Please wait while we delete the comment.',
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                willOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            
                            fetch(`/user/comments/${commentId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: 'Your comment has been deleted.',
                                        icon: 'success',
                                        confirmButtonColor: '#10b981',
                                        confirmButtonText: 'OK'
                                    });
                                    this.loadComments();
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: 'Error deleting comment: ' + (data.error || 'Unknown error'),
                                        icon: 'error',
                                        confirmButtonColor: '#ef4444',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error deleting comment:', error);
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Error deleting comment. Please try again.',
                                    icon: 'error',
                                    confirmButtonColor: '#ef4444',
                                    confirmButtonText: 'OK'
                                });
                            });
                        }
                    });
                },
                
                startReply(commentId) {
                    this.replyingTo = commentId;
                    this.replyContent = '';
                },
                
                cancelReply() {
                    this.replyingTo = null;
                    this.replyContent = '';
                },
                
                submitReply(parentCommentId) {
                    if (!this.replyContent.trim()) return;
                    
                    this.submittingReply = true;
                    const contentType = this.activeModal.category;
                    const contentId = this.activeModal.contentId;
                    
                    if (!contentId) {
                        this.submittingReply = false;
                        return;
                    }

                    fetch('/user/comments', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            content: this.replyContent,
                            content_type: contentType,
                            content_id: contentId,
                            parent_id: parentCommentId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.replyContent = '';
                            this.replyingTo = null;
                            this.loadComments();
                        } else {
                            alert('Error posting reply: ' + (data.error || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error posting reply:', error);
                        alert('Error posting reply. Please try again.');
                    })
                    .finally(() => {
                        this.submittingReply = false;
                    });
                },
                
                // Notification functions
                toggleNotifications() {
                    this.showNotifications = !this.showNotifications;
                    if (this.showNotifications) {
                        this.loadNotifications();
                    }
                },
                
                handleNotificationClick(notification) {
                    // Mark notification as read
                    this.markNotificationAsRead(notification.id);
                    
                    // Close notification dropdown
                    this.showNotifications = false;
                    
                    // Find and open the corresponding content
                    this.openContentFromNotification(notification);
                },
                
                markNotificationAsRead(notificationId) {
                    fetch(`/user/notifications/${notificationId}/read`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update notification count
                            this.notificationCount = data.unread_count || 0;
                            
                            // Update the notification in the list
                            const notification = this.notifications.find(n => n.id === notificationId);
                            if (notification) {
                                notification.is_read = true;
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error marking notification as read:', error);
                    });
                },
                
                openContentFromNotification(notification) {
                    // Extract content type and ID from notification
                    const contentType = notification.type; // 'announcement', 'event', or 'news'
                    const contentId = notification.content_id;
                    
                    if (!contentId) {
                        console.error('No content ID found in notification');
                        return;
                    }
                    
                    // Fetch the content details and open modal
                    this.fetchAndOpenContent(contentType, contentId);
                },
                
                fetchAndOpenContent(contentType, contentId) {
                    // Determine the correct endpoint based on content type
                    let endpoint = '';
                    switch(contentType) {
                        case 'announcement':
                            endpoint = `/user/content/announcement/${contentId}`;
                            break;
                        case 'event':
                            endpoint = `/user/content/event/${contentId}`;
                            break;
                        case 'news':
                            endpoint = `/user/content/news/${contentId}`;
                            break;
                        default:
                            console.error('Unknown content type:', contentType);
                            return;
                    }
                    
                    fetch(endpoint, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.content) {
                            const content = data.content;
                            
                            // Set up the modal data
                            this.activeModal = {
                                title: content.title,
                                body: content.content || content.description,
                                category: contentType,
                                contentId: content.id,
                                date: contentType === 'event' 
                                    ? `Date: ${content.event_date ? new Date(content.event_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'TBD'}`
                                    : `Posted: ${new Date(content.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`,
                                media: content.hasMedia || 'none',
                                mediaUrl: content.mediaUrl ? `{{ asset('storage/') }}/${content.mediaUrl}` : '',
                                allImageUrls: content.allImageUrls || [],
                                allVideoUrls: content.allVideoUrls || [],
                                videoUrl: content.hasMedia === 'both' && content.allVideoUrls 
                                    ? `{{ asset('storage/') }}/${content.allVideoUrls[0]}` 
                                    : (content.hasMedia === 'video' ? `{{ asset('storage/') }}/${content.mediaUrl}` : ''),
                                publisher: content.admin.role === 'superadmin' 
                                    ? 'MCC Administration' 
                                    : (content.admin.role === 'department_admin' 
                                        ? `${content.admin.department_display} Department` 
                                        : (content.admin.role === 'office_admin' 
                                            ? content.admin.office_display 
                                            : content.admin.username))
                            };
                        } else {
                            console.error('Error fetching content:', data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching content:', error);
                    });
                },
                
                removeNotification(notificationId) {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this action!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, remove it!',
                        cancelButtonText: 'Cancel',
                        background: '#ffffff',
                        customClass: {
                            popup: 'swal-popup-custom',
                            title: 'swal-title-custom',
                            content: 'swal-content-custom'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show loading
                            Swal.fire({
                                title: 'Removing...',
                                text: 'Please wait while we remove the notification.',
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                willOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            
                            fetch(`/user/notifications/${notificationId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Remove notification from the list
                                    this.notifications = this.notifications.filter(n => n.id !== notificationId);
                                    
                                    // Update notification count
                                    this.notificationCount = Math.max(0, this.notificationCount - 1);
                                    
                                    Swal.fire({
                                        title: 'Removed!',
                                        text: 'The notification has been removed.',
                                        icon: 'success',
                                        confirmButtonColor: '#10b981',
                                        confirmButtonText: 'OK'
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: 'Error removing notification: ' + (data.error || 'Unknown error'),
                                        icon: 'error',
                                        confirmButtonColor: '#ef4444',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error removing notification:', error);
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Error removing notification. Please try again.',
                                    icon: 'error',
                                    confirmButtonColor: '#ef4444',
                                    confirmButtonText: 'OK'
                                });
                            });
                        }
                    });
                },
                
                loadNotifications() {
                    fetch('/user/notifications', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.notifications !== undefined) {
                            // Detect new notifications
                            const newNotifications = data.notifications.filter(notification => {
                                return !this.lastNotificationIds.has(notification.id);
                            });
                            
                            // Update notifications
                            this.notifications = data.notifications;
                            this.notificationCount = data.unread_count || 0;
                            
                            // Update last notification IDs
                            this.lastNotificationIds = new Set(data.notifications.map(n => n.id));
                            
                            // Show toast for new notifications
                            if (newNotifications.length > 0) {
                                newNotifications.forEach(notification => {
                                    this.queueToast(notification);
                                });
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error loading notifications:', error);
                    });
                },
                
                queueToast(notification) {
                    // Add to queue
                    this.toastQueue.push(notification);
                    
                    // If no toast is currently showing, show this one
                    if (!this.toastVisible) {
                        this.showNextToast();
                    }
                },
                
                showNextToast() {
                    if (this.toastQueue.length === 0) {
                        return;
                    }
                    
                    // Get next toast from queue
                    this.currentToast = this.toastQueue.shift();
                    this.toastVisible = true;
                    this.toastDismissed = false;
                    
                    // Auto-dismiss after 5 seconds (minimum 2 seconds display)
                    setTimeout(() => {
                        if (this.toastVisible && !this.toastDismissed) {
                            this.dismissToast();
                        }
                    }, 5000);
                },
                
                dismissToast() {
                    this.toastDismissed = true;
                    
                    // Wait for dismiss animation
                    setTimeout(() => {
                        this.toastVisible = false;
                        this.currentToast = null;
                        this.toastDismissed = false;
                        
                        // Show next toast if any in queue
                        if (this.toastQueue.length > 0) {
                            setTimeout(() => {
                                this.showNextToast();
                            }, 300);
                        }
                    }, 400);
                },
                
                handleToastClick() {
                    if (this.currentToast) {
                        // Open the notification content
                        this.openContentFromNotification(this.currentToast);
                        this.dismissToast();
                    }
                },
                
                handleTouchStart(event) {
                    this.touchStartY = event.touches[0].clientY;
                    this.isSwiping = false;
                },
                
                handleTouchMove(event) {
                    if (!this.touchStartY) return;
                    
                    this.touchCurrentY = event.touches[0].clientY;
                    const deltaY = this.touchStartY - this.touchCurrentY;
                    
                    // Only handle upward swipes
                    if (deltaY > 10) {
                        this.isSwiping = true;
                        const toast = this.$refs.toast;
                        if (toast) {
                            toast.classList.add('swiping');
                            const translateY = Math.min(0, -deltaY);
                            toast.style.transform = `translateX(-50%) translateY(${translateY}px)`;
                        }
                    }
                },
                
                handleTouchEnd(event) {
                    if (!this.isSwiping) return;
                    
                    const deltaY = this.touchStartY - this.touchCurrentY;
                    const toast = this.$refs.toast;
                    
                    if (toast) {
                        toast.classList.remove('swiping');
                        toast.style.transform = '';
                    }
                    
                    // If swiped up more than 50px, dismiss
                    if (deltaY > 50) {
                        this.dismissToast();
                    }
                    
                    this.touchStartY = 0;
                    this.touchCurrentY = 0;
                    this.isSwiping = false;
                },
                
                // Name validation functions
                validateNameInput(value) {
                    // Remove all numbers and symbols, keep only letters and spaces
                    return value.replace(/[^a-zA-Z\s]/g, '');
                },
                
                validateNameWithHyphen(value) {
                    // Remove all numbers and symbols except hyphens
                    let cleaned = value.replace(/[^a-zA-Z\s\-]/g, '');
                    
                    // Ensure hyphens are only in the middle (not at start or end)
                    // Remove hyphens at the beginning
                    cleaned = cleaned.replace(/^-+/, '');
                    // Remove hyphens at the end
                    cleaned = cleaned.replace(/-+$/, '');
                    // Replace multiple consecutive hyphens with single hyphen
                    cleaned = cleaned.replace(/-{2,}/g, '-');
                    
                    return cleaned;
                },
                
                // Profile management functions
                initializeProfileForm() {
                    // Convert short codes to full department names if needed
                    let department = '{{ auth()->user()->department }}';
                    const shortToFullDepartmentMap = {
                        'BSIT': 'Bachelor of Science in Information Technology',
                        'BSBA': 'Bachelor of Science in Business Administration',
                        'BEED': 'Bachelor of Elementary Education',
                        'BSHM': 'Bachelor of Science in Hospitality Management',
                        'BSED': 'Bachelor of Secondary Education'
                    };
                    
                    // Convert year level to proper case
                    let yearLevel = '{{ auth()->user()->year_level }}';
                    const yearLevelMap = {
                        '1st year': '1st Year',
                        '2nd year': '2nd Year',
                        '3rd year': '3rd Year',
                        '4th year': '4th Year'
                    };
                    
                    this.profileForm = {
                        first_name: '{{ auth()->user()->first_name }}',
                        middle_name: '{{ auth()->user()->middle_name }}',
                        surname: '{{ auth()->user()->surname }}',
                        department: shortToFullDepartmentMap[department] || department,
                        year_level: yearLevelMap[yearLevel] || yearLevel
                    };
                    // Reset scroll position when entering edit mode
                    this.$nextTick(() => {
                        if (this.$refs.modalContent) {
                            this.$refs.modalContent.scrollTop = 0;
                        }
                    });
                },
                
                resetProfileForm() {
                    this.initializeProfileForm();
                    // Reset scroll position when canceling edit mode
                    this.$nextTick(() => {
                        if (this.$refs.modalContent) {
                            this.$refs.modalContent.scrollTop = 0;
                        }
                    });
                },
                
                updateProfile() {
                    if (this.updatingProfile) return;
                    
                    this.updatingProfile = true;
                    
                    fetch('/user/profile/update', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(this.profileForm)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonColor: '#10b981',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                this.editMode = false;
                                // Reload page to reflect changes
                                window.location.reload();
                            });
                        } else {
                            let errorMessage = 'Error updating profile.';
                            if (data.errors) {
                                errorMessage = Object.values(data.errors).flat().join('\\n');
                            } else if (data.message) {
                                errorMessage = data.message;
                            }
                            
                            Swal.fire({
                                title: 'Error!',
                                text: errorMessage,
                                icon: 'error',
                                confirmButtonColor: '#ef4444',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error updating profile:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Error updating profile. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#ef4444',
                            confirmButtonText: 'OK'
                        });
                    })
                    .finally(() => {
                        this.updatingProfile = false;
                    });
                },
                
                handleProfilePictureChange(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    
                    // Validate file type
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                    if (!allowedTypes.includes(file.type)) {
                        Swal.fire({
                            title: 'Invalid File Type',
                            text: 'Please select a JPG or PNG image file.',
                            icon: 'error',
                            confirmButtonColor: '#ef4444',
                            confirmButtonText: 'OK'
                        });
                        event.target.value = '';
                        return;
                    }
                    
                    // Validate file size (5MB max)
                    if (file.size > 5 * 1024 * 1024) {
                        Swal.fire({
                            title: 'File Too Large',
                            text: 'Please select an image smaller than 5MB.',
                            icon: 'error',
                            confirmButtonColor: '#ef4444',
                            confirmButtonText: 'OK'
                        });
                        event.target.value = '';
                        return;
                    }
                    
                    // Show preview and upload
                    this.uploadProfilePicture(file);
                },
                
                uploadProfilePicture(file) {
                    if (this.uploadingPicture) return;
                    
                    this.uploadingPicture = true;
                    
                    // Show loading
                    Swal.fire({
                        title: 'Uploading...',
                        text: 'Please wait while we upload your profile picture.',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    const formData = new FormData();
                    formData.append('profile_picture', file);
                    
                    fetch('/user/profile/upload-picture', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonColor: '#10b981',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Update the profile image in the modal
                                if (this.$refs.profileImage) {
                                    this.$refs.profileImage.src = data.profile_picture_url;
                                }
                                // Reload page to reflect changes everywhere
                                window.location.reload();
                            });
                        } else {
                            let errorMessage = 'Error uploading profile picture.';
                            if (data.errors && data.errors.profile_picture) {
                                errorMessage = data.errors.profile_picture[0];
                            } else if (data.message) {
                                errorMessage = data.message;
                            }
                            
                            Swal.fire({
                                title: 'Error!',
                                text: errorMessage,
                                icon: 'error',
                                confirmButtonColor: '#ef4444',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error uploading profile picture:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Error uploading profile picture. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#ef4444',
                            confirmButtonText: 'OK'
                        });
                    })
                    .finally(() => {
                        this.uploadingPicture = false;
                    });
                },
                
                removeProfilePicture() {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'This will remove your current profile picture.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, remove it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show loading
                            Swal.fire({
                                title: 'Removing...',
                                text: 'Please wait while we remove your profile picture.',
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                willOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            
                            fetch('/user/profile/remove-picture', {
                                method: 'DELETE',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Removed!',
                                        text: data.message,
                                        icon: 'success',
                                        confirmButtonColor: '#10b981',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        // Reload page to reflect changes
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: data.message || 'Error removing profile picture.',
                                        icon: 'error',
                                        confirmButtonColor: '#ef4444',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error removing profile picture:', error);
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Error removing profile picture. Please try again.',
                                    icon: 'error',
                                    confirmButtonColor: '#ef4444',
                                    confirmButtonText: 'OK'
                                });
                            });
                        }
                    });
                },
                
                // Logout function with SweetAlert
                logout() {
                    // Check if mobile device
                    const isMobile = window.innerWidth <= 768;
                    
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You will be logged out of your account.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, logout!',
                        cancelButtonText: 'Cancel',
                        // Mobile responsive settings
                        width: isMobile ? '90%' : '400px',
                        padding: isMobile ? '1rem' : '1.5rem',
                        customClass: {
                            popup: isMobile ? 'mobile-swal-popup' : '',
                            title: isMobile ? 'mobile-swal-title' : '',
                            content: isMobile ? 'mobile-swal-content' : '',
                            confirmButton: isMobile ? 'mobile-swal-button' : '',
                            cancelButton: isMobile ? 'mobile-swal-button' : ''
                        },
                        buttonsStyling: true,
                        allowOutsideClick: !isMobile,
                        backdrop: true,
                        focusConfirm: false,
                        focusCancel: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show loading
                            Swal.fire({
                                title: 'Logging out...',
                                text: 'Please wait while we log you out.',
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                width: isMobile ? '90%' : '400px',
                                padding: isMobile ? '1rem' : '1.5rem',
                                customClass: {
                                    popup: isMobile ? 'mobile-swal-popup' : '',
                                    title: isMobile ? 'mobile-swal-title' : '',
                                    content: isMobile ? 'mobile-swal-content' : ''
                                },
                                willOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            
                            // Perform logout
                            fetch('/user/logout', {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                credentials: 'same-origin'
                            })
                            .then(response => {
                                console.log('Logout response status:', response.status);
                                if (response.ok) {
                                    return response.json();
                                } else {
                                    console.error('Logout failed with status:', response.status);
                                    throw new Error('Logout failed');
                                }
                            })
                            .then(data => {
                                console.log('Logout successful:', data);
                                
                                // Clear any cached data
                                if ('caches' in window) {
                                    caches.keys().then(names => {
                                        names.forEach(name => {
                                            caches.delete(name);
                                        });
                                    });
                                }
                                
                                // Clear localStorage and sessionStorage
                                try {
                                    localStorage.clear();
                                    sessionStorage.clear();
                                } catch (e) {
                                    console.warn('Could not clear storage:', e);
                                }
                                
                                Swal.fire({
                                    title: 'Logged out!',
                                    text: data.message || 'You have been successfully logged out.',
                                    icon: 'success',
                                    confirmButtonText: 'OK',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false
                                }).then(() => {
                                    // Use the redirect URL from server response if available
                                    const redirectUrl = data.redirect || '/login';
                                    window.location.replace(redirectUrl);
                                });
                            })
                            .catch(error => {
                                console.error('Logout error:', error);
                                
                                // Clear storage even on error for security
                                try {
                                    localStorage.clear();
                                    sessionStorage.clear();
                                } catch (e) {
                                    console.warn('Could not clear storage:', e);
                                }
                                
                                Swal.fire({
                                    title: 'Session Ended',
                                    text: 'Your session has been terminated for security. You will be redirected to login.',
                                    icon: 'warning',
                                    confirmButtonText: 'OK',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false
                                }).then(() => {
                                    // Force redirect for security
                                    window.location.replace('/login');
                                });
                            });
                            
                            // Fallback timeout - if logout takes too long, redirect anyway
                            setTimeout(() => {
                                if (document.querySelector('.swal2-container')) {
                                    Swal.close();
                                    window.location.href = '/login';
                                }
                            }, 10000); // 10 second timeout
                        }
                    });
                }
            }
        }

        // Session timeout management
        class SessionManager {
            constructor() {
                this.sessionLifetime = {{ config('session.lifetime', 60) }} * 60 * 1000; // Convert to milliseconds
                this.inactivityTimeout = 30 * 60 * 1000; // 30 minutes
                this.warningTime = 5 * 60 * 1000; // Show warning 5 minutes before timeout
                this.lastActivity = Date.now();
                this.warningShown = false;
                this.timeoutWarning = null;
                
                this.init();
            }
            
            init() {
                // Track user activity
                this.trackActivity();
                
                // Start session monitoring
                this.startMonitoring();
                
                // Handle page visibility changes
                this.handleVisibilityChange();
            }
            
            trackActivity() {
                const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
                
                events.forEach(event => {
                    document.addEventListener(event, () => {
                        this.updateActivity();
                    }, { passive: true });
                });
            }
            
            updateActivity() {
                this.lastActivity = Date.now();
                this.warningShown = false;
                
                if (this.timeoutWarning) {
                    Swal.close();
                    this.timeoutWarning = null;
                }
            }
            
            startMonitoring() {
                setInterval(() => {
                    this.checkTimeout();
                }, 60000); // Check every minute
            }
            
            checkTimeout() {
                const now = Date.now();
                const timeSinceActivity = now - this.lastActivity;
                
                // Check if we should show warning
                if (timeSinceActivity >= (this.inactivityTimeout - this.warningTime) && !this.warningShown) {
                    this.showTimeoutWarning();
                }
                
                // Check if session has timed out
                if (timeSinceActivity >= this.inactivityTimeout) {
                    this.handleTimeout();
                }
            }
            
            showTimeoutWarning() {
                this.warningShown = true;
                const remainingTime = Math.ceil((this.inactivityTimeout - (Date.now() - this.lastActivity)) / 1000 / 60);
                
                this.timeoutWarning = Swal.fire({
                    title: 'Session Timeout Warning',
                    html: `Your session will expire in <strong>${remainingTime} minutes</strong> due to inactivity.<br><br>Click "Stay Logged In" to continue your session.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Stay Logged In',
                    cancelButtonText: 'Logout Now',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    timer: 5 * 60 * 1000, // Auto-close after 5 minutes
                    timerProgressBar: true,
                    customClass: {
                        popup: 'session-warning-popup'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // User chose to stay logged in
                        this.updateActivity();
                        this.sendHeartbeat();
                    } else if (result.isDismissed) {
                        // User chose to logout or timer expired
                        this.handleTimeout();
                    }
                });
            }
            
            sendHeartbeat() {
                // Send a heartbeat to server to keep session alive
                fetch('/api/heartbeat', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Background-Request': 'true'
                    },
                    credentials: 'same-origin'
                }).catch(error => {
                    console.warn('Heartbeat failed:', error);
                });
            }
            
            handleTimeout() {
                Swal.fire({
                    title: 'Session Expired',
                    text: 'Your session has expired due to inactivity. You will be redirected to the login page.',
                    icon: 'info',
                    confirmButtonText: 'Login Again',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then(() => {
                    // Clear any cached data
                    this.clearClientData();
                    
                    // Redirect to login
                    window.location.replace('/login');
                });
            }
            
            handleVisibilityChange() {
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden) {
                        // Page became visible, update activity
                        this.updateActivity();
                    }
                });
            }
            
            clearClientData() {
                try {
                    localStorage.clear();
                    sessionStorage.clear();
                    
                    if ('caches' in window) {
                        caches.keys().then(names => {
                            names.forEach(name => {
                                caches.delete(name);
                            });
                        });
                    }
                } catch (e) {
                    console.warn('Could not clear client data:', e);
                }
            }
        }

        // Simple video play functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize session manager
            new SessionManager();
            
            document.querySelectorAll('.play-button').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const container = this.closest('.announcement-item');
                    container.click();
                });
            });
        });
    </script>
</body>
</html>
