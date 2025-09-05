import os
import logging
import requests
from urllib.parse import urlencode
import random
import string
from datetime import datetime
from telegram import InlineKeyboardButton, InlineKeyboardMarkup, Update
from telegram.ext import CallbackContext
from dotenv import load_dotenv
from telegram import Update, InlineKeyboardButton, InlineKeyboardMarkup
from telegram.ext import ApplicationBuilder, CommandHandler, ContextTypes, CallbackContext

# Load environment variables from .env file
load_dotenv()
BOT_TOKEN = os.getenv("TELEGRAM_BOT_TOKEN")

# url_plug = "http://localhost/alltrenders/codes/Telegram_Bot/Roynek%20Grows%20Bot" #used for testing
url_plug = "https://roynek.com/alltrenders/codes/Telegram_Bot/Roynek%20Grows%20Bot"
game_url = f'{url_plug}/pre_game.html'
# game_url = f'{url_plug}/game_club.html'
calendar_url = "https://docs.google.com/document/d/1lfuj6zKsNyK16RrOSvDD2AmgFedJAaR-2b5xTJwX6iw/edit?usp=sharing"  # Replace with your proposed calendar URL
telegram_channel_url = "https://t.me/roynek_grows"  # Replace with your actual Telegram channel URL

# Set up logging
logging.basicConfig(
    format='%(asctime)s - %(name)s - %(levelname)s',
    level=logging.INFO
)


def generate_strong_password(length=12):
    characters = string.ascii_letters + string.digits + string.punctuation
    password = ''.join(random.choice(characters) for i in range(length))
    return password

async def the_main(update: Update, context: CallbackContext, command="start"):
    user = update.message.from_user
    user_id = user.id
    username = user.username
    first_name = user.first_name
    last_name = user.last_name
    referrer_id = context.args[0] if context.args else None

    now = datetime.now()
    formattedDate = now.strftime('%Y-%m-%d')
    # formattedTime = now.strftime('%H:%M:%S')
    formattedTime = now.strftime('%I:%M:%S %p')

    
    response = requests.post(f'{url_plug}/check_user.php', data={
        'username': username, 
        'tele_id': user_id, 
        'referrer_id': referrer_id,
        'first_name': first_name,
        'last_name': last_name
    })
    result = response.json()
    # print(result)
    print("checking the user")

    if result["status"] == True:
        print("User exists. Login")
        query_params = {
            'hash': result["hash"],
            'tele_id': user_id,
            'username': username,
            'first_name': first_name,
            'last_name': last_name
        }
        game_url_with_params = f"{game_url}?{urlencode(query_params)}"
        
        # Create buttons
        keyboard = [
            [InlineKeyboardButton("Play Now", url=game_url_with_params)],
            [InlineKeyboardButton("View Our Calendar", url=calendar_url)],
            [InlineKeyboardButton("Join Our Telegram Channel", url=telegram_channel_url)]
        ]
        reply_markup = InlineKeyboardMarkup(keyboard)

        # Create the welcoming message
        welcome_message = (
            f"🎉 Welcome {username}! 🎉 You are now a Roynekian with Grows Powers \n\n"
            "We're thrilled to have you here. Unlike other Telegram token games, "
            "we are committed and sure of our launch date.\n\n"
            "Click the button below to start playing the game: You can only use this button once. \n\n"
            "To enjoy another session, give the /play command again \n\n"
            "Stay updated with our proposed calendar by clicking the link below.\n\n"
            "Join our Telegram channel for the latest updates and community discussions."
        ) if (command=="start") else (
            "Click the button below to start playing the game: It is a one time button, you can not use it again. \n\n "
            "We have improved the security of telegram games. To enjoy another session, give the /play command again \n\n"
            "Stay updated with our proposed calendar by clicking the link below.\n\n"
            "Join our Telegram channel for the latest updates and community discussions."
        )

        await update.message.reply_text(welcome_message, reply_markup=reply_markup)

        # keyboard = [[InlineKeyboardButton("Play the Game", url=game_url_with_params)]]
        # reply_markup = InlineKeyboardMarkup(keyboard)
        # await update.message.reply_text('Click the button below to start playing the game:', reply_markup=reply_markup)
    else:
        print("User does not exist. Registering now.")
        response = requests.post(f'{url_plug}/register_user.php', data={
            'username': username, 
            'tele_id': user_id, 
            'referrer_id': referrer_id,
            'first_name': first_name,
            'last_name': last_name,
            'email': None,
            'password': generate_strong_password(),
            'third_party_id': user_id,
            'signup_date': formattedDate,
            'signup_time': formattedTime,
            # 'directed_by': referrer_id,
        })
        result = response.json()
        # print(result)
        print("registration data.")

        if result["status"] == True:
            print("User exists. Login")
            query_params = {
                'hash': result["hash"],
                'tele_id': user_id,
                'username': username,
                'first_name': first_name,
                'last_name': last_name
            }
            game_url_with_params = f"{game_url}?{urlencode(query_params)}"
            # Create buttons
            keyboard = [
                [InlineKeyboardButton("Play Now", url=game_url_with_params)],
                [InlineKeyboardButton("View Our Calendar", url=calendar_url)],
                [InlineKeyboardButton("Join Our Telegram Channel", url=telegram_channel_url)]
            ]
            reply_markup = InlineKeyboardMarkup(keyboard)

            # Create the welcoming message
            welcome_message = (
                f"🎉 Welcome {username}! 🎉 You are now a Roynekian with Grows Powers \n\n"
                "We're thrilled to have you here. Unlike other Telegram token games, "
                "we are committed and sure of our launch date.\n\n"
                "Click the button below to start playing the game:\n\n"
                "Stay updated with our proposed calendar by clicking the link below.\n\n"
                "Join our Telegram channel for the latest updates and community discussions."
            ) if (command=="start") else (
                "Click the button below to start playing the game:\n\n"
                "Stay updated with our proposed calendar by clicking the link below.\n\n"
                "Join our Telegram channel for the latest updates and community discussions."
            )

            await update.message.reply_text(welcome_message, reply_markup=reply_markup)
            # keyboard = [[InlineKeyboardButton("Play the Game", url=game_url_with_params)]]
            # reply_markup = InlineKeyboardMarkup(keyboard)
            # await update.message.reply_text('Click the button below to start playing the game:', reply_markup=reply_markup)
        else:
            await update.message.reply_text('We are having some issues. We are working on fixing it, Hope to see you around.')
    

async def start(update: Update, context: CallbackContext):
    await the_main(update=update, context=context, command="start")
        # After registration, proceed with login or further actions as necessary

    # if referrer_id:
    #     # New user registration with referrer
    #     response = requests.post(f'{url_plug}/check_user.php', data={'username': username, 'tele_id': user_id, 'referrer_id': referrer_id})
    # else:
    #     # Regular user registration
    #     response = requests.post(f'{url_plug}/register.php', data={'username': username, 'user_id': user_id})

    # result = response.json()

    # if result['status'] == 'success':
    #     referral_link = f"https://t.me/RoynekGrowsBot?start={user_id}"
    #     await update.message.reply_text(f'Welcome to the Gaming Bot! You have been registered successfully. Use /play to start playing the game.')
    # else:
    #     await update.message.reply_text('Registration failed: ' + result['message'])

async def play(update: Update, context: CallbackContext):
    await the_main(update=update, context=context, command="play")
    # keyboard = [[InlineKeyboardButton("Play the Game", url=game_url)]]
    # reply_markup = InlineKeyboardMarkup(keyboard)
    
    # await update.message.reply_text('Click the button below to start playing the game:', reply_markup=reply_markup)

async def referral(update: Update, context: CallbackContext):
    user = update.message.from_user
    user_id = user.id
    referral_link = f"https://t.me/RoynekGrowsBot?start={user_id}"
    await update.message.reply_text(f'Invite your friends and family to play and you both earn some Roynek Grows coins. Share and earn: {referral_link}')

def main():
    application = ApplicationBuilder().token(BOT_TOKEN).build()
    
    start_handler = CommandHandler('start', start)
    play_handler = CommandHandler('play', play)
    referral_handler = CommandHandler('referral', referral)
    
    application.add_handler(start_handler)
    application.add_handler(play_handler)
    application.add_handler(referral_handler)
    

    application.run_polling()

if __name__ == '__main__':
    main()
