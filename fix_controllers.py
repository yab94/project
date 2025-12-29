#!/usr/bin/env python3
import re
import sys
from pathlib import Path

def fix_controller(file_path):
    """Fix render calls in a controller to use proper View composition"""
    with open(file_path, 'r') as f:
        content = f.read()
    
    # Check if View is imported
    if 'use App\\Core\\Infrastructure\\Web\\View\\View;' not in content:
        # Add the import after the last use statement
        content = re.sub(
            r'(use App\\Core\\Infrastructure\\Web\\Routing\\Router;)',
            r'\1\nuse App\\Core\\Infrastructure\\Web\\View\\View;',
            content
        )
    
    # Pattern to match: $this->render(new View('path', [
    # and replace with composition
    pattern = r"\$this->render\(new View\('([^']+)',\s*\[\s*\n((?:.*?\n)*?)\s*\]\)\);"
    
    def replace_render(match):
        view_path = match.group(1)
        data_content = match.group(2)
        
        # Extract the data array content
        lines = data_content.strip().split('\n')
        data_lines = []
        for line in lines:
            stripped = line.strip()
            if stripped and not stripped.startswith('//'):
                data_lines.append('            ' + stripped)
        
        # Build the new code
        return f"""$content = new View('{view_path}', [
            'url' => $this->urlGenerator(),
{chr(10).join(data_lines)}
        ]);
        $this->render(new View('layout/default', [
            'content' => $content->render()
        ]));"""
    
    content = re.sub(pattern, replace_render, content, flags=re.MULTILINE)
    
    with open(file_path, 'w') as f:
        f.write(content)
    
    print(f"Fixed: {file_path}")

if __name__ == '__main__':
    project_root = Path('/home/yab/DEV/project')
    
    # Find all controllers except AbstractController
    controllers = list(project_root.glob('src/**/Controller/*Controller.php'))
    controllers = [c for c in controllers if 'Abstract' not in c.name]
    
    for controller in controllers:
        try:
            fix_controller(controller)
        except Exception as e:
            print(f"Error fixing {controller}: {e}")
