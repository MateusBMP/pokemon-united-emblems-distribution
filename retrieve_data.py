import json
from selenium import webdriver
from selenium.webdriver.common.by import By

driver = webdriver.Firefox()
driver.get("https://unite-db.com/boost-emblems")
el = driver.find_element(By.CSS_SELECTOR, "section.content > div:first-child")
data_as_string = el.get_attribute("innerHTML")
driver.quit()

data = json.loads(data_as_string)
with open("data.json", "w") as f:
    json.dump(data, f)
