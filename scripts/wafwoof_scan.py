#!/usr/bin/env python3
"""
wafwoof Security Scan Integration Script
This script provides automated security scanning using wafwoof
"""

import subprocess
import sys
import json
import datetime
import os

def run_wafwoof_scan(domain):
    """
    Run wafwoof scan on the specified domain
    """
    try:
        # Check if wafwoof is installed
        result = subprocess.run(['wafw00f', '--version'], 
                              capture_output=True, 
                              text=True,
                              timeout=5)
        
        if result.returncode != 0:
            print("Error: wafwoof is not installed.")
            print("Install it with: pip install wafw00f")
            return None
            
    except FileNotFoundError:
        print("Error: wafwoof is not installed.")
        print("Install it with: pip install wafw00f")
        print("\nAlternatively, you can:")
        print("  git clone https://github.com/EnableSecurity/wafw00f.git")
        print("  cd wafw00f")
        print("  python setup.py install")
        return None
    
    # Run wafwoof scan
    print(f"\nRunning wafwoof scan on: {domain}")
    print("=" * 50)
    
    try:
        result = subprocess.run(['wafw00f', '-a', '-v', domain],
                              capture_output=True,
                              text=True,
                              timeout=30)
        
        return {
            'success': result.returncode == 0,
            'output': result.stdout + result.stderr,
            'timestamp': datetime.datetime.now().isoformat(),
            'domain': domain
        }
        
    except subprocess.TimeoutExpired:
        return {
            'success': False,
            'output': 'Scan timed out after 30 seconds',
            'timestamp': datetime.datetime.now().isoformat(),
            'domain': domain
        }
    except Exception as e:
        return {
            'success': False,
            'output': str(e),
            'timestamp': datetime.datetime.now().isoformat(),
            'domain': domain
        }

def save_results(results, filename=None):
    """
    Save scan results to file
    """
    if filename is None:
        timestamp = datetime.datetime.now().strftime('%Y%m%d_%H%M%S')
        filename = f'security_reports/wafwoof_scan_{timestamp}.json'
    
    os.makedirs('security_reports', exist_ok=True)
    
    with open(filename, 'w') as f:
        json.dump(results, f, indent=2)
    
    print(f"\nResults saved to: {filename}")
    return filename

def main():
    # Default domain
    domain = 'http://localhost:8000'
    
    # Get domain from command line argument if provided
    if len(sys.argv) > 1:
        domain = sys.argv[1]
    
    print("=" * 50)
    print("MCC News Aggregator - wafwoof Security Scan")
    print("=" * 50)
    
    # Run scan
    results = run_wafwoof_scan(domain)
    
    if results:
        # Display results
        print("\n" + "=" * 50)
        print("SCAN RESULTS")
        print("=" * 50)
        print(results['output'])
        
        # Save results
        filename = save_results(results)
        
        # Summary
        print("\n" + "=" * 50)
        print("SUMMARY")
        print("=" * 50)
        print(f"Domain: {results['domain']}")
        print(f"Status: {'Success' if results['success'] else 'Failed'}")
        print(f"Timestamp: {results['timestamp']}")
        print(f"Report: {filename}")
        
        # Check for WAF detection
        if 'WAF' in results['output'].upper() or 'detected' in results['output'].lower():
            print("\n✓ WAF Detected - DDoS protection is active")
        else:
            print("\n⚠ WAF not detected - Consider configuring CDN")
            
    else:
        print("\nScan failed. Please install wafwoof first.")

if __name__ == '__main__':
    main()

