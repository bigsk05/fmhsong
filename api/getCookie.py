import requests,json


def main():
    cookies=[""]
    session=requests.session()
    info={"phone":"","md5_password":cookies[0]}
    cookie=json.loads(session.post("https://music-netease.vercel.app/login/cellphone",data=info).text)["cookie"]
    with open("cookie.conf","w+") as fp:
        fp.write(cookie)

if __name__=="__main__":
    main()