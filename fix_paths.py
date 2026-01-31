import os
import re

# Base directory
base_dir = r"d:\XAMPP\htdocs\php"
dirs_to_fix = [
    os.path.join(base_dir, "HTML"),
    os.path.join(base_dir, "includes"),
    os.path.join(base_dir, "actions")
]

# Folders that should be relative
resource_folders = [
    "CSS", "JS", "ẢNH", "ANH_SAN_PHAM", "lens máy ảnh", 
    "ảnh chi tiết", "logo", "actions", "includes"
]

def fix_paths(content):
    # Fix resource paths: /FOLDER/ -> ../FOLDER/
    # This also helps actions/ redirecting to ../HTML/ if handled correctly?
    # Actually wait.
    # If in HTML/ and linking to /CSS/ -> ../CSS/
    # If in actions/ and linking to /HTML/ -> ../HTML/
    # If in includes/ and linking to /CSS/ -> ../CSS/ (assuming includes are used in HTML)
    
    # Generic replacement: "/(FOLDER)/" -> "../$1/"
    # We must be careful not to double dot if it's already there (user edits).
    
    # List of folders to convert to parent relative
    folders_regex = "|".join([re.escape(f) for f in resource_folders])
    
    # Regex 1: /FOLDER/ -> ../FOLDER/
    # Look for quote, forward slash, folder name, forward slash
    # Avoid replacing if it's already ../
    
    pattern1 = r'(["\'])/(%s)/' % folders_regex
    
    def replacer1(match):
        quote = match.group(1)
        folder = match.group(2)
        return f'{quote}../{folder}/'
    
    new_content = re.sub(pattern1, replacer1, content)
    
    # Regex 2: /HTML/ -> ../HTML/ (for actions or cross linking if needed)
    # Actually if in HTML folder, linking to /HTML/file.php should be just file.php
    # But files in HTML folder link to other files in HTML folder usually by just filename.
    # If they use /HTML/filename.php, we should strip /HTML/ prefix IF we are in HTML folder.
    
    # But simpler approach: Convert /HTML/ to ../HTML/ generally works?
    # HTML/file.php -> ../HTML/file.php (Up to php root, then HTML).
    # This works for files in HTML and actions folders.
    
    pattern2 = r'(["\'])/HTML/'
    replacement2 = r'\1../HTML/'
    
    new_content = re.sub(pattern2, replacement2, new_content)
    
    return new_content

def process_file(filepath):
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        new_content = fix_paths(content)
        
        if new_content != content:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(new_content)
            print(f"Fixed: {filepath}")
        else:
            print(f"No changes: {filepath}")
            
    except Exception as e:
        print(f"Error processing {filepath}: {e}")

if __name__ == "__main__":
    for directory in dirs_to_fix:
        if os.path.exists(directory):
            for filename in os.listdir(directory):
                if filename.endswith(".php") or filename.endswith(".html"):
                    process_file(os.path.join(directory, filename))
        else:
            print(f"Directory not found: {directory}")
            
    print("Done fixing paths.")
